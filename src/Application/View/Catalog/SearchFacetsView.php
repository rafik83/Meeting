<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Catalog;

use Proximum\Vimeet\Domain\Model\Catalog\Internal\SearchFacet;

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
     * @return bool
     */
    public function hasType(): bool
    {
        // if Category is activated, Type cannot be activated
        return $this->hasFilter(SearchFacet::TYPE_TYPE) && !$this->hasFilter(SearchFacet::TYPE_CATEGORY);
    }

    /**
     * @return bool
     */
    public function hasCategory(): bool
    {
        return null !== $this->getCategory();
    }

    /**
     * @return SearchFacetView|null
     */
    public function getType(): ?SearchFacetView
    {
        if (!$this->hasType()) {
            return null;
        }

        return $this->getFilter(SearchFacet::TYPE_TYPE);
    }

    /**
     * @return SearchFacetView|false
     */
    public function getPosition(): ?SearchFacetView
    {
        return $this->getFilter(SearchFacet::TYPE_POSITION);
    }

    /**
     * @return SearchFacetView|null
     */
    public function getCategory(): ?SearchFacetView
    {
        return $this->getFilter(SearchFacet::TYPE_CATEGORY);
    }

    /**
     * @return SearchFacetView|null
     */
    public function getKeywords(): ?SearchFacetView
    {
        return $this->getFilter(SearchFacet::TYPE_KEYWORDS);
    }

    /**
     * @return SearchFacetView|null
     */
    public function getLocalization(): ?SearchFacetView
    {
        return $this->getFilter(SearchFacet::TYPE_LOCALIZATION);
    }

    /**
     * @return SearchFacetView|null
     */
    public function getOrganizationCategory(): ?SearchFacetView
    {
        return $this->getFilter(SearchFacet::TYPE_ORGANIZATION_CATEGORY);
    }

    /**
     * @param string $filter
     *
     * @return SearchFacetView|null
     */
    private function getFilter($filter): ?SearchFacetView
    {
        foreach ($this->searchFacets as $facetView) {
            if ($facetView->getType() === $filter && $facetView->isEnabled()) {
                return $facetView;
            }
        }

        return null;
    }

    /**
     * @param string $filter
     *
     * @return bool
     */
    private function hasFilter($filter): bool
    {
        return null !== $this->getFilter($filter);
    }
}
