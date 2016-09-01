<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Elastica\Query;
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
     * @param PaginatedFinderInterface $finder
     */
    public function __construct(PaginatedFinderInterface $finder)
    {
        $this->finder = $finder;
    }

    /**
     * {@inheritdoc}
     */
    public function find(Event $event, array $filters, array $orderBy, $page, $limit, $locale)
    {
        $builder = new SheetSearchQueryBuilder($event, $filters);
        $query   = new Query($builder->getQuery());

        if (count($orderBy)) {
            foreach ($orderBy as $value) {
                if (Constant::ORDER_BY_ALPHABETICAL === $value) {
                    $query->addSort(['sheetName.raw' => 'asc']);
                } else if(Constant::ORDER_BY_DATE_ADDED_TO_CATALOG === $value) {
                    $query->addSort(['inCatalogAt' => 'desc']);
                }
            }
        }

        try {
            $result = $this->finder->findPaginated($query)->setMaxPerPage($limit)->setCurrentPage($page);
        } catch (NotValidCurrentPageException $exception) {
            throw new UnavailableCurrentPageException(sprintf('Current page %s not available', $page));
        }

        return new PaginatedResult($result->getCurrentPageResults(), $page, $limit, $result->getNbResults());
    }
}
