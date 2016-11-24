<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
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
                'day'       => $day->getDay(),
                'startTime' => $day->getStartTime(),
                'endTime'   => $day->getEndTime(),
            ];
        }

        usort($this->days, function ($day1, $day2) {
            return $day1['day'] > $day2['day'];
        });
    }
}
