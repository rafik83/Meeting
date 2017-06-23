<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Catalog\External;

use Proximum\Vimeet\Domain\Model\Catalog\AbstractSearchFacet;
use Proximum\Vimeet\Domain\Model\Event;

class SearchFacet extends AbstractSearchFacet
{
    /**
     * SearchFacet constructor.
     *
     * @param Event  $event
     * @param string $type
     * @param bool   $enabled
     */
    public function __construct(Event $event, string $type, bool $enabled = false)
    {
        parent::__construct($event, $type, $enabled);
    }
}
