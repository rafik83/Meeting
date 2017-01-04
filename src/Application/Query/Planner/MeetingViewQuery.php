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

class MeetingViewQuery
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var array
     */
    public $sheets;

    /**
     * @var array
     */
    public $participants;

    /**
     * @param Event $event
     * @param array $sheets
     * @param array $participants
     */
    public function __construct(Event $event, array $sheets, array $participants)
    {
        $this->event        = $event;
        $this->sheets       = $sheets;
        $this->participants = $participants;
    }
}
