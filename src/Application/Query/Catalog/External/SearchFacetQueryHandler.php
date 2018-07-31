<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Catalog\External;

use Proximum\Vimeet\Domain\Model\Catalog\External\SearchFacet;
use Proximum\Vimeet\Domain\Repository\Catalog\External\SearchFacetRepositoryInterface;

class SearchFacetQueryHandler
{
    /** @var SearchFacetRepositoryInterface */
    private $searchFacetRepository;

    /**
     * @param SearchFacetRepositoryInterface $searchFacetRepository
     */
    public function __construct(SearchFacetRepositoryInterface $searchFacetRepository)
    {
        $this->searchFacetRepository = $searchFacetRepository;
    }

    /**
     * @param SearchFacetQuery $query
     *
     * @return SearchFacet[]
     */
    public function handle(SearchFacetQuery $query)
    {
        $searchFacets = $this->searchFacetRepository->getByEvent($query->event);

        if (empty($searchFacets)) {
            foreach ($query->types as $type) {
                $searchFacets[] = new SearchFacet($query->event, $type);
            }
        }

        return $searchFacets;
    }
}
