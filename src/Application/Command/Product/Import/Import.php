<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Product\Import;

use Proximum\Vimeet\Domain\Model\Event;

class Import
{
    /**
     * Event to import the products
     *
     * @var Event
     */
    public $currentEvent;

    /**
     * Event with the products to import
     * @var Event
     */
    public $event;

    /**
     * @param Event $currentEvent
     */
    public function __construct(Event $currentEvent)
    {
        $this->currentEvent = $currentEvent;
    }
}
