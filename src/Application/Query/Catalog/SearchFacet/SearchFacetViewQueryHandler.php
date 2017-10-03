<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Catalog\SearchFacet;

use Proximum\Vimeet\Application\View\Catalog\SearchFacetsView;
use Proximum\Vimeet\Application\View\Catalog\SearchFacetView;
use Proximum\Vimeet\Domain\Repository\SearchFacetRepositoryInterface;

class SearchFacetViewQueryHandler implements SearchFacetQueryHandlerInterface
{
    /**
     * @var SearchFacetRepositoryInterface
     */
    private $searchFacetRepository;

    /**
     * SearchFacetViewQueryHandler constructor.
     *
     * @param SearchFacetRepositoryInterface $searchFacetRepository
     */
    public function __construct(SearchFacetRepositoryInterface $searchFacetRepository)
    {
        $this->searchFacetRepository = $searchFacetRepository;
    }

    /**
     * @param AbstractSearchFacetViewQuery $query
     *
     * @return SearchFacetsView
     */
    public function handle(AbstractSearchFacetViewQuery $query): SearchFacetsView
    {
        $searchFacets = $this->searchFacetRepository->getByEvent($query->event);

        $searchFacetViews = [];

        foreach ($searchFacets as $facet) {
            $searchFacetViews[] = new SearchFacetView(
                $facet->getType(),
                $facet->getLabel($query->locale),
                $facet->getPlaceholder($query->locale),
                $facet->isEnabled()
            );
        }

        return new SearchFacetsView($searchFacetViews);
    }
}
