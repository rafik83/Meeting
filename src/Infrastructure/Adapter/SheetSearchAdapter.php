<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Elastica\Aggregation\Filter;
use Elastica\Aggregation\Nested;
use Elastica\Aggregation\Terms;
use Elastica\Query;
use Elastica\SearchableInterface;
use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
use FOS\ElasticaBundle\Paginator\PaginatorAdapterInterface;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Application\Exception\Paginator\UnavailableCurrentPageException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Sheet\Constant;

class SheetSearchAdapter implements SheetSearchAdapterInterface
{
    /**
     * @var PaginatedFinderInterface Elastica finder
     */
    private $finder;

    /**
     * @var SearchableInterface
     */
    private $searchable;

    /**
     * @param PaginatedFinderInterface $finder
     * @param SearchableInterface      $searchable
     */
    public function __construct(PaginatedFinderInterface $finder, SearchableInterface $searchable)
    {
        $this->finder     = $finder;
        $this->searchable = $searchable;
    }

    /**
     * {@inheritdoc}
     */
    public function find(Event $event, array $filters, $orderBy, $page, $limit, $locale, $getAggregations)
    {
        $builder = new SheetSearchQueryBuilder($event, $filters, $locale);
        $query   = new Query($builder->getQuery());

        if (Constant::ORDER_BY_DATE_ADDED_TO_CATALOG === $orderBy) {
            $query->addSort(['inCatalogAt' => 'desc']);
        } elseif (Constant::ORDER_BY_ALPHABETICAL === $orderBy) {
            $query->addSort(['sheetName.raw' => 'asc']);
        } else {
            $query->addSort(['_score' => 'desc']);
        }

        if (true === $getAggregations) {
            $query->addAggregation($this->getAggregation(self::ES_FIELD_TYPE));
            $query->addAggregation($this->getAggregation(self::ES_FIELD_ORGANIZATION_CATEGORY));
            $query->addAggregation($this->getNestedAggregation(self::ES_FIELD_POSITION));
        }

        try {
            $result = $this->finder->findPaginated($query)->setMaxPerPage($limit)->setCurrentPage($page);
        } catch (NotValidCurrentPageException $exception) {
            throw new UnavailableCurrentPageException(sprintf('Current page %s not available', $page));
        }

        /** @var PaginatorAdapterInterface $paginatorAdapter */
        $paginatorAdapter = $result->getAdapter();

        return new PaginatedResult(
            $result->getCurrentPageResults(),
            $page,
            $limit,
            $result->getNbResults(),
            true === $getAggregations ? $paginatorAdapter->getAggregations() : null
        );
    }

    /**
     * {@inheritdoc}
     */
    public function findLocalization(Event $event, $filter, $locale)
    {
        // city
        $match = new Query\Match();
        $match->setField('city_autocomplete', $filter);

        $filterQuery = new \Elastica\Filter\Query();
        $filterQuery->setQuery($match);

        $citiesAggregations = new Terms('cities');
        $citiesAggregations->setField('city');
        $citiesAggregations->setSize(10);

        $cities = new Filter('cities_aggs');
        $cities->addAggregation($citiesAggregations);
        $cities->setFilter($filterQuery);

        // zipcode
        $matchZipcode = new Query\Match();
        $matchZipcode->setField('zipcode_autocomplete', $filter);

        $filterZipcodeQuery = new \Elastica\Filter\Query();
        $filterZipcodeQuery->setQuery($matchZipcode);

        $zipcodeAggregations = new Terms('zipcodes');
        $zipcodeAggregations->setField('zipcode');
        $zipcodeAggregations->setSize(10);

        $zipcodes = new Filter('zipcode_aggs');
        $zipcodes->addAggregation($zipcodeAggregations);
        $zipcodes->setFilter($filterZipcodeQuery);

        // country
        $matchCountry = new Query\Match('country.label_autocomplete', $filter);
        $matchLocale  = new Query\Match('country.locale', $locale);

        $boolQuery = new Query\Bool();
        $boolQuery->addMust($matchCountry);
        $boolQuery->addMust($matchLocale);

        $filterCountryQuery = new \Elastica\Filter\Query();
        $filterCountryQuery->setQuery($boolQuery);

        $countryAggregations = new Terms('countries');
        $countryAggregations->setField('country.label');
        $countryAggregations->setSize(10);

        $filterCountries = new Filter('countries_filter');
        $filterCountries->addAggregation($countryAggregations);
        $filterCountries->setFilter($filterCountryQuery);

        $nestedCountryAggregations = new \Elastica\Aggregation\Nested('countries_aggs', 'country');
        $nestedCountryAggregations->addAggregation($filterCountries);

        $query = new Query();
        $query->addAggregation($cities)
          ->addAggregation($zipcodes)
          ->addAggregation($nestedCountryAggregations)
          ->setSize(0);

        return $this->searchable->search($query)->getAggregations();
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeAggregations(Event $event, $locale, array $filters, $filterToRemove)
    {
        return $this->searchAggregations($event, $locale, $filters, $filterToRemove, self::ES_FIELD_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganizationCategoryAggregations(Event $event, $locale, array $filters, $filterToRemove)
    {
        return $this->searchAggregations(
            $event,
            $locale,
            $filters,
            $filterToRemove,
            self::ES_FIELD_ORGANIZATION_CATEGORY
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPositionAggregations(Event $event, $locale, array $filters, $filterToRemove)
    {
        return $this->searchAggregations(
            $event,
            $locale,
            $filters,
            $filterToRemove,
            self::ES_FIELD_POSITION
        );
    }

    /**
     * @param Event  $event
     * @param string $locale
     * @param array  $filters
     * @param string $filterToRemove
     * @param string $elasticField
     *
     * @return array
     */
    private function searchAggregations(Event $event, $locale, array $filters, $filterToRemove, $elasticField)
    {
        // remove filter
        unset($filters[$filterToRemove]);

        // add inCatalog filter
        $filters = array_merge([SheetSearchAdapterInterface::ES_FIELD_IN_CATALOG => true], $filters);

        $builder = new SheetSearchQueryBuilder($event, $filters, $locale);
        $query   = new Query($builder->getQuery());

        if($elasticField === self::ES_FIELD_POSITION) {
            $query->addAggregation($this->getNestedAggregation($elasticField));
        } else {
            $query->addAggregation($this->getAggregation($elasticField));
        }

        $query->setSize(0);

        $result = $this->searchable->search($query);

        return $result->getAggregations();
    }

    /**
     * @param string $field
     *
     * @return Terms
     */
    private function getAggregation($field)
    {
        $aggregation = new Terms($field);
        $aggregation->setField($field);

        return $aggregation;
    }

    private function getNestedAggregation($field)
    {
        $nested = new Nested($field, 'participants');
        $nested->addAggregation($this->getAggregation($field));

        return $nested;
    }
}
