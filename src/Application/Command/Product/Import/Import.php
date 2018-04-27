<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
    public $toEvent;

    /**
     * Event with the products to import
     *
     * @var Event
     */
    public $event;

    /**
     * @param Event $toEvent
     */
    public function __construct(Event $toEvent)
    {
        $this->toEvent = $toEvent;
    }
}
