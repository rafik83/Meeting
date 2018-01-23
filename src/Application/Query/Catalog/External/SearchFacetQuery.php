<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Catalog\External;

use Proximum\Vimeet\Domain\Model\Catalog\External\SearchFacet;
use Proximum\Vimeet\Domain\Model\Event;

class SearchFacetQuery
{
    /** @var Event */
    public $event;

    /** @var array of types */
    public $types;

    /**
     * SearchFacetQuery constructor.
     *
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->types = SearchFacet::getAllTypes();
    }
}
