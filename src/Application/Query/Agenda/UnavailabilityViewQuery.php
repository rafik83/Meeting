<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Unavailability;

class UnavailabilityViewQuery
{
    /**
     * @var Unavailability
     */
    public $unavailability;

    /**
     * @var Event
     */
    public $event;

    /**
     * @param Unavailability $unavailability
     * @param Event          $event
     */
    public function __construct(Unavailability $unavailability, Event $event)
    {
        $this->unavailability = $unavailability;
        $this->event          = $event;
    }
}
