<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\Day;
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
     * @var Day
     */
    public $day;

    /**
     * @param Unavailability $unavailability
     * @param Event          $event
     * @param Day            $day
     */
    public function __construct(Unavailability $unavailability, Event $event, Day $day)
    {
        $this->unavailability = $unavailability;
        $this->event          = $event;
        $this->day            = $day;
    }
}
