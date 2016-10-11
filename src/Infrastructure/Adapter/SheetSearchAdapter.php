<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter;

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
        $builder = new SheetSearchQueryBuilder($event, $filters);
        $query   = new Query($builder->getQuery());

        if (Constant::ORDER_BY_DATE_ADDED_TO_CATALOG === $orderBy) {
            $query->addSort(['inCatalogAt' => 'desc']);
        } else {
            $query->addSort(['sheetName.raw' => 'asc']);
        }

        if (true === $getAggregations) {
            $query->addAggregation($this->getAggregation(self::ES_FIELD_TYPE));
            $query->addAggregation($this->getAggregation(self::ES_FIELD_ORGANIZATION_CATEGORY));
            $query->addAggregation($this->getAggregation(self::ES_FIELD_POSITION));
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
    public function getTypeAggregations(Event $event, array $filters, $filterToRemove)
    {
        return $this->searchAggregations($event, $filters, $filterToRemove, self::ES_FIELD_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganizationCategoryAggregations(Event $event, array $filters, $filterToRemove)
    {
        return $this->searchAggregations($event, $filters, $filterToRemove, self::ES_FIELD_ORGANIZATION_CATEGORY);
    }

    /**
     * @param Event  $event
     * @param array  $filters
     * @param string $filterToRemove
     * @param string $elasticField
     *
     * @return array
     */
    private function searchAggregations(Event $event, array $filters, $filterToRemove, $elasticField)
    {
        // remove filter
        unset($filters[$filterToRemove]);

        // add inCatalog filter
        $filters = array_merge([SheetSearchAdapterInterface::ES_FIELD_IN_CATALOG => true], $filters);

        $builder = new SheetSearchQueryBuilder($event, $filters);
        $query   = new Query($builder->getQuery());
        $query->addAggregation($this->getAggregation($elasticField));
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
}
