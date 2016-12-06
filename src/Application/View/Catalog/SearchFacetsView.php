<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Catalog;

use Proximum\Vimeet\Domain\Model\SearchFacet;

class SearchFacetsView
{
    /**
     * @var SearchFacetView[]
     */
    private $searchFacets;

    /**
     * SearchFacetsView constructor.
     *
     * @param SearchFacetView[] $searchFacets
     */
    public function __construct(array $searchFacets)
    {
        $this->searchFacets = $searchFacets;
    }

    /**
     * @return SearchFacetView
     */
    public function hasType()
    {
        return $this->hasFilter(SearchFacet::TYPE_TYPE);
    }

    /**
     * @return SearchFacetView|false
     */
    public function hasPosition()
    {
        return $this->hasFilter(SearchFacet::TYPE_POSITION);
    }

    /**
     * @return SearchFacetView|false
     */
    public function hasCategory()
    {
        return $this->hasFilter(SearchFacet::TYPE_CATEGORY);
    }

    /**
     * @return SearchFacetView|false
     */
    public function hasKeywords()
    {
        return $this->hasFilter(SearchFacet::TYPE_KEYWORDS);
    }

    /**
     * @return SearchFacetView|false
     */
    public function hasLocalization()
    {
        return $this->hasFilter(SearchFacet::TYPE_LOCALIZATION);
    }

    /**
     * @return SearchFacetView|false
     */
    public function hasOrganizationCategory()
    {
        return $this->hasFilter(SearchFacet::TYPE_ORGANIZATION_CATEGORY);
    }

    /**
     * @param string $filter
     *
     * @return SearchFacetView|false
     */
    private function hasFilter($filter)
    {
        foreach ($this->searchFacets as $facetView) {
            if ($facetView->getType() === $filter && $facetView->isEnabled()) {
                return $facetView;
            }
        }

        return false;
    }
}
