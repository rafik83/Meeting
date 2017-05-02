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

class CancelAttendanceUnavailabilityViewQuery
{
    /** @var Event */
    public $event;

    /** @var Event\Day */
    public $day;

    /**
     * @param Event     $event
     * @param Event\Day $day
     */
    public function __construct(Event $event, Event\Day $day)
    {
        $this->event = $event;
        $this->day   = $day;
    }
}
