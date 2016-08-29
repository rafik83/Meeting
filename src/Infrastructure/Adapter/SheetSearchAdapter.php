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
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class SheetSearchAdapter implements SheetSearchAdapterInterface
{
    /**
     * @var PaginatedFinderInterface Elastica finder
     */
    private $finder;

    /**
     * @var SheetRepositoryInterface
     */
    private $repository;

    /**
     * SheetSearchAdapter constructor.
     *
     * @param PaginatedFinderInterface $finder
     * @param SheetRepositoryInterface $repository
     */
    public function __construct(PaginatedFinderInterface $finder, SheetRepositoryInterface $repository)
    {
        $this->finder     = $finder;
        $this->repository = $repository;
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

        $sheets = $this->repository->findFullSheets($result->getCurrentPageResults());

        return new PaginatedResult($sheets, $page, $limit, $result->getNbResults());
    }
}
