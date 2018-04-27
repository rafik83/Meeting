<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event\Day;

use Proximum\Vimeet\Domain\Model\Event;

class Update
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var array
     */
    public $days;

    /**
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->days  = [];

        foreach ($event->getDays() as $day) {
            $this->days[] = [
                'startTime' => $day->getStartTime(),
                'endTime'   => $day->getEndTime(),
            ];
        }
    }
}
