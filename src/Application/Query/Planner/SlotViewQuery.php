<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Planner;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Application\View\Planner\DayView;

class SlotViewQuery
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var DayView[]
     */
    public $days;

    /**
     * @param Event     $event
     * @param DayView[] $days
     */
    public function __construct(Event $event, array $days)
    {
        $this->event = $event;
        $this->days  = $days;
    }
}
