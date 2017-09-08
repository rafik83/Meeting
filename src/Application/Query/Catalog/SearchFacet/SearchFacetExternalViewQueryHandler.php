<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Catalog\SearchFacet;

use Proximum\Vimeet\Application\View\Catalog\SearchFacetsView;
use Proximum\Vimeet\Application\View\Catalog\SearchFacetView;
use Proximum\Vimeet\Domain\Repository\Catalog\External\SearchFacetRepositoryInterface;

class SearchFacetExternalViewQueryHandler implements SearchFacetQueryHandlerInterface
{
    /**
     * @var SearchFacetRepositoryInterface
     */
    private $externalSearchFacetRepository;

    /**
     * SearchFacetExternalViewQueryHandler constructor.
     *
     * @param SearchFacetRepositoryInterface $externalSearchFacetRepository
     */
    public function __construct(SearchFacetRepositoryInterface $externalSearchFacetRepository)
    {
        $this->externalSearchFacetRepository = $externalSearchFacetRepository;
    }
    
    /**
     * @param AbstractSearchFacetViewQuery $query
     *
     * @return SearchFacetsView
     */
    public function handle(AbstractSearchFacetViewQuery $query): SearchFacetsView
    {
        $searchFacets = $this->externalSearchFacetRepository->getByEvent($query->event);
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
