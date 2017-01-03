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
use Proximum\Vimeet\Application\View\Planner\Day;

class SlotViewQuery
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var Day[]
     */
    public $days;

    /**
     * @param Event $event
     * @param Day[] $days
     */
    public function __construct(Event $event, array $days)
    {
        $this->event = $event;
        $this->days  = $days;
    }
}
