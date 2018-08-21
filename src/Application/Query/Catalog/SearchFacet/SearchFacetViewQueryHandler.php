<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Catalog\SearchFacet;

use Proximum\Vimeet\Application\View\Catalog\SearchFacetsView;
use Proximum\Vimeet\Application\View\Catalog\SearchFacetView;
use Proximum\Vimeet\Application\View\Catalog\TagFilterView;
use Proximum\Vimeet\Domain\Model\Catalog\CatalogTagFilter;
use Proximum\Vimeet\Domain\Repository\Catalog\CatalogTagFilterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SearchFacetRepositoryInterface;

class SearchFacetViewQueryHandler implements SearchFacetQueryHandlerInterface
{
    /** @var SearchFacetRepositoryInterface */
    private $searchFacetRepository;

    /** @var CatalogTagFilterRepositoryInterface */
    private $catalogTagFilterRepository;

    /**
     * @param SearchFacetRepositoryInterface      $searchFacetRepository
     * @param CatalogTagFilterRepositoryInterface $catalogTagFilterRepository
     */
    public function __construct(
        SearchFacetRepositoryInterface $searchFacetRepository,
        CatalogTagFilterRepositoryInterface $catalogTagFilterRepository
    ) {
        $this->searchFacetRepository = $searchFacetRepository;
        $this->catalogTagFilterRepository = $catalogTagFilterRepository;
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

        $catalogTagFilters = $this->catalogTagFilterRepository->getByEventAndType($query->event, CatalogTagFilter::TYPE_INTERNAL);
        $catalogTagFilterViews = [];

        foreach ($catalogTagFilters as $catalogTagFilter) {
            $catalogTagFilterViews[$catalogTagFilter->getTag()] = new TagFilterView(
                $catalogTagFilter->getTag(),
                $catalogTagFilter->getLabel($query->locale),
                $catalogTagFilter->getPlaceholder($query->locale)
            );
        }

        return new SearchFacetsView(
            $searchFacetViews,
            $catalogTagFilterViews
        );
    }
}
