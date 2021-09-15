<?php

namespace Proximum\Vimeet\Application\View\Catalog;

use Proximum\Vimeet\Domain\Model\Catalog\Internal\SearchFacet;

class SearchFacetsView
{
    /** @var SearchFacetView[] */
    private $searchFacets;

    /** @var TagFilterView[] */
    private $tagFilterViews;

    /**
     * @param SearchFacetView[] $searchFacets
     * @param TagFilterView[]   $tagFilterViews
     */
    public function __construct(array $searchFacets, array $tagFilterViews = [])
    {
        $this->searchFacets = $searchFacets;
        $this->tagFilterViews = $tagFilterViews;
    }

    /**
     * @return bool
     */
    public function hasType(): bool
    {
        return $this->hasFilter(SearchFacet::TYPE_TYPE);
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
     * @return SearchFacetView|null
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

    /**
     * @return TagFilterView[]
     */
    public function getTagFilterViews(): array
    {
        return $this->tagFilterViews;
    }
}
