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
use Proximum\Vimeet\Application\View\Planner\ParticipantView;
use Proximum\Vimeet\Application\View\Planner\SheetView;
use Proximum\Vimeet\Application\View\Planner\SlotView;

class MeetingViewQuery
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var SheetView[]
     */
    public $sheets;

    /**
     * @var ParticipantView[]
     */
    public $participants;

    /**
     * @var SlotView[]
     */
    public $slots;

    /**
     * @param Event             $event
     * @param SheetView[]       $sheets
     * @param ParticipantView[] $participants
     * @param SlotView[]        $slots
     */
    public function __construct(Event $event, array $sheets, array $participants, array $slots)
    {
        $this->event        = $event;
        $this->sheets       = $sheets;
        $this->participants = $participants;
        $this->slots        = $slots;
    }
}
