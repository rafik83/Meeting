<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Catalog\Internal\SearchFacet;
use Proximum\Vimeet\Domain\Model\Event;

interface SearchFacetRepositoryInterface
{
    /**
     * @param SearchFacet $searchFacet
     */
    public function set(SearchFacet $searchFacet);

    /**
     * @param SearchFacet $searchFacet
     */
    public function add(SearchFacet $searchFacet);

    /**
     * @param Event $event
     *
     * @return SearchFacet[]
     */
    public function getByEvent(Event $event);
}
