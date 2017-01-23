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
use Elastica\Filter\Query as FilterQuery;
use Elastica\Filter\Term;
use Elastica\Query;
use Elastica\Query\FunctionScore;
use Elastica\Result;
use Elastica\SearchableInterface;
use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
use FOS\ElasticaBundle\Paginator\PaginatorAdapterInterface;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Application\Exception\Paginator\UnavailableCurrentPageException;
use Proximum\Vimeet\Application\Query\Messaging\Campaign\SheetListView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Sheet\Constant;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;

class SheetSearchAdapter implements SheetSearchAdapterInterface
{
    const NOMENCLATURE_ITEMS_WEIGHT = 1.1;

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
    public function find(
        Event $event,
        array $filters,
        $orderBy,
        $page,
        $limit,
        $locale,
        $getAggregations,
        $nomenclatureItems = []
    ) {
        $nomenclatureBoost = (isset($nomenclatureItems[Nomenclature::OBJECTIVE_NONE])) ? count($nomenclatureItems[Nomenclature::OBJECTIVE_NONE]) : 0;

        $builder = new SheetSearchQueryBuilder($event, $filters, $locale, $nomenclatureBoost, $nomenclatureItems);

        if (Constant::ORDER_BY_DATE_ADDED_TO_CATALOG === $orderBy) {
            $query = new Query($builder->getQuery());
            $query->addSort(['inCatalogAt' => 'desc']);

        } elseif (Constant::ORDER_BY_RELEVANCE === $orderBy &&
            isset($nomenclatureItems[Nomenclature::OBJECTIVE_NONE])
        ) {
            $functionScore = new FunctionScore();
            $functionScore->setScoreMode(FunctionScore::SCORE_MODE_SUM);

            foreach ($nomenclatureItems[Nomenclature::OBJECTIVE_NONE] as $key) {
                $nested = new \Elastica\Filter\Nested();
                $nested->setFilter((new Term())->setTerm('nomenclatureItems.key', $key));
                $nested->setPath('nomenclatureItems');
                $functionScore->addFunction('weight', [], $nested, self::NOMENCLATURE_ITEMS_WEIGHT);
            }

            $query = $functionScore->setQuery($builder->getQuery());
            $query = new Query($query);
            $query->addSort(['_score' => 'desc']);
        } elseif (Constant::ORDER_BY_CREATED_AT === $orderBy) {
            $query   = new Query($builder->getQuery());
            $query->addSort(['createdAt' => 'desc']);
        } else {
            $query = new Query($builder->getQuery());
            $query->addSort(['sheetName.raw' => 'asc']);
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
    public function getSheetListView(Event $event, array $filters, $locale)
    {
        $builder = new SheetSearchQueryBuilder($event, $filters, $locale);

        $query = new Query($builder->getQuery());
        $query->addSort(['sheetName' => 'asc']);

        $options = ["size" => 100000];

        return array_map(function (Result $sheet) {
            return new SheetListView($sheet->id, $sheet->sheetName);
        }, $this->searchable->search($query, $options)->getResults());
    }

    /**
     * {@inheritdoc}
     */
    public function findLocalization(Event $event, $filter, $locale)
    {
        $builder = new SheetSearchQueryBuilder(
            $event,
            [SheetSearchAdapterInterface::ES_FIELD_IN_CATALOG => true],
            $locale
        );

        $query = new Query($builder->getQuery());
        $query->addAggregation($this->findCityQuery($event, $filter))
            ->addAggregation($this->findCountryQuery($event, $filter, $locale))
            ->setSize(0);

        return $this->searchable->search($query)->getAggregations();
    }

    /**
     * {@inheritdoc}
     */
    public function findKeyword(Event $event, $filter, $locale)
    {
        $builder = new SheetSearchQueryBuilder(
            $event,
            [SheetSearchAdapterInterface::ES_FIELD_IN_CATALOG => true],
            $locale
        );

        $matchKeyword = new Query\Term(['keywords.label_autocomplete' => $filter]);
        $matchLocale  = new Query\Match('keywords.locale', $locale);

        $boolQuery = new Query\Bool();
        $boolQuery->addMust($matchKeyword);
        $boolQuery->addMust($matchLocale);

        $filterKeywordQuery = new FilterQuery();
        $filterKeywordQuery->setQuery($boolQuery);

        $filterEventQuery = new FilterQuery();
        $filterEventQuery->setQuery(new Query\Match('event', $event->getId()));

        $keywordAggregations = new Terms('keyword');
        $keywordAggregations->setField('keywords.label');
        $keywordAggregations->setSize(10);

        $filterKeywords = new Filter('keywords_filter');
        $filterKeywords->addAggregation($keywordAggregations);
        $filterKeywords->setFilter($filterKeywordQuery);

        $nestedKeywordsAggregations = new Nested('keywords_aggs', 'keywords');
        $nestedKeywordsAggregations->addAggregation($filterKeywords);

        $filterKeywordsEvent = new Filter('keywords', $filterEventQuery);
        $filterKeywordsEvent->addAggregation($nestedKeywordsAggregations);

        $query = new Query($builder->getQuery());
        $query->addAggregation($filterKeywordsEvent)
            ->addAggregation($this->findSheetnameQuery($event, $filter))
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

        if ($elasticField === self::ES_FIELD_POSITION) {
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

    /**
     * @param Event  $event
     * @param string $filter
     *
     * @return Filter
     */
    private function findCityQuery(Event $event, $filter)
    {
        $matchCity = new Query\Bool();
        $matchCity->addMust(new Query\Match('event', $event->getId()));
        $matchCity->addMust(new Query\Match('city_autocomplete', $filter));

        $filterQuery = new FilterQuery();
        $filterQuery->setQuery($matchCity);

        $citiesAggregations = new Terms('cities');
        $citiesAggregations->setField('city');
        $citiesAggregations->setSize(10);

        $cities = new Filter('cities_aggs');
        $cities->addAggregation($citiesAggregations);
        $cities->setFilter($filterQuery);

        return $cities;
    }

    /**
     * @param Event  $event
     * @param string $filter
     * @param string $locale
     *
     * @return Filter
     */
    private function findCountryQuery(Event $event, $filter, $locale)
    {
        $filterEventQuery = new FilterQuery();
        $filterEventQuery->setQuery(new Query\Match('event', $event->getId()));

        // country
        $matchCountry = new Query\Match('country.label_autocomplete', $filter);
        $matchLocale  = new Query\Match('country.locale', $locale);

        $boolQuery = new Query\Bool();
        $boolQuery->addMust($matchCountry);
        $boolQuery->addMust($matchLocale);

        $filterCountryQuery = new FilterQuery();
        $filterCountryQuery->setQuery($boolQuery);

        $countryAggregations = new Terms('countries');
        $countryAggregations->setField('country.label');
        $countryAggregations->setSize(10);

        $filterCountries = new Filter('countries_filter');
        $filterCountries->addAggregation($countryAggregations);
        $filterCountries->setFilter($filterCountryQuery);

        $nestedCountryAggregations = new Nested('countries', 'country');
        $nestedCountryAggregations->addAggregation($filterCountries);

        $filterCountryEvent = new Filter('countries_aggs', $filterEventQuery);
        $filterCountryEvent->addAggregation($nestedCountryAggregations);

        return $filterCountryEvent;
    }

    /**
     * @param Event  $event
     * @param string $filter
     *
     * @return Filter
     */
    private function findSheetnameQuery(Event $event, $filter)
    {
        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust(new Query\Term(['sheetName.autocomplete' => $filter]));
        $boolQuery->addMust(new Query\Match('event', $event->getId()));

        $sheetAggregation = new Terms('sheetname');
        $sheetAggregation->setField('sheetName.raw');

        $filterQuery     = new FilterQuery($boolQuery);
        $filterSheetname = new Filter('sheet', $filterQuery);
        $filterSheetname->addAggregation($sheetAggregation);

        return $filterSheetname;
    }
}
