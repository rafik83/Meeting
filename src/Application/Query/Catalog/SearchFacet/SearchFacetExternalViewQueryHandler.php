<?php

namespace Proximum\Vimeet\Application\Query\Catalog\SearchFacet;

use Proximum\Vimeet\Application\View\Catalog\SearchFacetsView;
use Proximum\Vimeet\Application\View\Catalog\SearchFacetView;
use Proximum\Vimeet\Application\View\Catalog\TagFilterView;
use Proximum\Vimeet\Domain\Model\Catalog\CatalogTagFilter;
use Proximum\Vimeet\Domain\Repository\Catalog\CatalogTagFilterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Catalog\External\SearchFacetRepositoryInterface;

class SearchFacetExternalViewQueryHandler implements SearchFacetQueryHandlerInterface
{
    /** @var SearchFacetRepositoryInterface */
    private $externalSearchFacetRepository;

    /** @var CatalogTagFilterRepositoryInterface */
    private $catalogTagFilterRepository;

    public function __construct(
        SearchFacetRepositoryInterface $externalSearchFacetRepository,
        CatalogTagFilterRepositoryInterface $catalogTagFilterRepository
    ) {
        $this->externalSearchFacetRepository = $externalSearchFacetRepository;
        $this->catalogTagFilterRepository = $catalogTagFilterRepository;
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

        $catalogTagFilters = $this->catalogTagFilterRepository->getByEventAndType($query->event, CatalogTagFilter::TYPE_EXTERNAL);
        $catalogTagFilterViews = [];

        foreach ($catalogTagFilters as $catalogTagFilter) {
            $tag = $catalogTagFilter->getTag();

            $catalogTagFilterViews[$tag] = new TagFilterView(
                $tag,
                $catalogTagFilter->getLabel($query->locale),
                $catalogTagFilter->getPlaceholder($query->locale)
            );
        }

        return new SearchFacetsView($searchFacetViews, $catalogTagFilterViews);
    }
}
