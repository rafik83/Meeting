<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event\SearchFacet;

use Proximum\Vimeet\Application\Command\Catalog\External\ConfigureSearchFacet;
use Proximum\Vimeet\Domain\Model\Catalog\Internal\SearchFacet;
use Proximum\Vimeet\Domain\Model\Event;

class Update extends ConfigureSearchFacet
{
    /**
     * @param Event         $event
     * @param SearchFacet[] $searchFacets
     */
    public function __construct(Event $event, array $searchFacets)
    {
        $this->event = $event;
        $this->persistedSearchFacets = $searchFacets;
        $this->prepareSearchFacetFields();
    }
}
