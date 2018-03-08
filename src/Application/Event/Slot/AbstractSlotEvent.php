<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event\Slot;

use Proximum\Vimeet\Domain\Model\Event as DomainEvent;
use Symfony\Component\EventDispatcher\Event;

abstract class AbstractSlotEvent extends Event
{
    /** @var DomainEvent */
    public $event;

    /**
     * @param DomainEvent $event
     */
    public function __construct(DomainEvent $event)
    {
        $this->event = $event;
    }
}
