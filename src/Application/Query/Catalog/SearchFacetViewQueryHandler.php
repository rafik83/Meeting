<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Catalog;

use Proximum\Vimeet\Application\View\Catalog\SearchFacetsView;
use Proximum\Vimeet\Application\View\Catalog\SearchFacetView;
use Proximum\Vimeet\Domain\Repository\SearchFacetRepositoryInterface;

class SearchFacetViewQueryHandler
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
     * @param SearchFacetViewQuery $query
     *
     * @return SearchFacetsView
     */
    public function handle(SearchFacetViewQuery $query)
    {
        $searchFacets = $this->searchFacetRepository->getByEvent($query->event);

        $searchFacetViews = [];

        foreach ($searchFacets as $facet) {
            $translation = $facet->getTranslation($query->locale);

            $searchFacetViews[] = new SearchFacetView(
                $facet->getType(),
                $translation !== null ? $translation->getLabel() : '',
                $translation !== null ? $translation->getPlaceholder() : '',
                $facet->isEnabled()
            );
        }

        return new SearchFacetsView($searchFacetViews);
    }
}
