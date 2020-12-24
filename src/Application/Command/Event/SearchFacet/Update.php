<?php

namespace Proximum\Vimeet\Application\Command\Event\SearchFacet;

use Proximum\Vimeet\Application\Command\Catalog\External\ConfigureSearchFacet;
use Proximum\Vimeet\Domain\Model\Catalog\CatalogTagFilter;
use Proximum\Vimeet\Domain\Model\Catalog\Internal\SearchFacet;
use Proximum\Vimeet\Domain\Model\Event;

class Update extends ConfigureSearchFacet
{
    /**
     * @param Event              $event
     * @param SearchFacet[]      $searchFacets
     * @param CatalogTagFilter[] $catalogTagFilters
     */
    public function __construct(Event $event, array $searchFacets, array $catalogTagFilters)
    {
        parent::__construct($catalogTagFilters);

        $this->event = $event;
        $this->persistedSearchFacets = $searchFacets;
        $this->prepareSearchFacetFields();
        $this->type = CatalogTagFilter::TYPE_INTERNAL;
    }
}
