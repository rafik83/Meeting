<?php

namespace Proximum\Vimeet\Application\Command\Catalog\External;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Catalog\External\SearchFacet;
use Proximum\Vimeet\Domain\Model\Event;

class SetSearchFacet implements Command
{
    /** @var array */
    public $searchFacets;

    /** @var Event */
    public $event;

    /** @var SearchFacet[] */
    public $persistedSearchFacets;

    /**
     * @param Event         $event
     * @param array         $searchFacets
     * @param SearchFacet[] $persistedSearchFacets
     */
    public function __construct(Event $event, array $searchFacets, array $persistedSearchFacets)
    {
        $this->event = $event;
        $this->searchFacets = $searchFacets;
        $this->persistedSearchFacets = $persistedSearchFacets;
    }
}
