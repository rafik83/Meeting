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
    public function find(Event $event, array $filters, $orderBy, $page, $limit, $locale)
    {
        $builder = new SheetSearchQueryBuilder($event, $filters);
        $query   = new Query($builder->getQuery());

        if (null !== $orderBy) {
            if (Constant::ORDER_BY_ALPHABETICAL === $orderBy) {
                $query->addSort(['sheetName.raw' => 'asc']);
            } elseif (Constant::ORDER_BY_DATE_ADDED_TO_CATALOG === $orderBy) {
                $query->addSort(['inCatalogAt' => 'desc']);
            }
        }

        try {
            $result = $this->finder->findPaginated($query)->setMaxPerPage($limit)->setCurrentPage($page);
        } catch (NotValidCurrentPageException $exception) {
            throw new UnavailableCurrentPageException(sprintf('Current page %s not available', $page));
        }

        return new PaginatedResult($result->getCurrentPageResults(), $page, $limit, $result->getNbResults());
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeStats(Event $event, array $filters)
    {
        return $this->getStats($event, $filters, 'type');
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganizationCategoryStats(Event $event, array $filters)
    {
        return $this->getStats($event, $filters, 'organizationCategory');
    }

    /**
     * @param Event  $event
     * @param array  $filters
     * @param string $field
     *
     * @return array
     */
    private function getStats(Event $event, array $filters, $field)
    {
        $builder = new SheetSearchQueryBuilder($event, $filters);
        $query   = new Query($builder->getQuery());

        $aggregation = new Terms($field);
        $aggregation->setField($field);

        $query->addAggregation($aggregation);
        $query->setSize(0);

        $result        = $this->searchable->search($query);
        $stats         = [];
        $elementsCount = $result->getAggregations()[$field]['buckets'];

        foreach ($elementsCount as $element) {
            $stats[$element['key']] = $element['doc_count'];
        }

        return $stats;
    }
}
