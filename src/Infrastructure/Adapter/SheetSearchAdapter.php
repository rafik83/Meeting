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
            $query->addSort($orderBy);
        }

        try {
            $result = $this->finder->findPaginated($query)->setMaxPerPage($limit)->setCurrentPage($page);
        } catch (NotValidCurrentPageException $ex) {
            throw new UnavailableCurrentPageException(sprintf('Current page %s not available', $page));
        }

        return new PaginatedResult($result->getCurrentPageResults(), $page, $limit, $result->getNbResults());
    }
}
