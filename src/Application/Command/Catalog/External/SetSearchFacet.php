<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Catalog\External;

use Proximum\Vimeet\Domain\Model\Catalog\External\SearchFacet;
use Proximum\Vimeet\Domain\Model\Event;

class SetSearchFacet
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
