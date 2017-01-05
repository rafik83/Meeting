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
use Proximum\Vimeet\Application\View\Planner\SheetView;
use Proximum\Vimeet\Application\View\Planner\SlotView;

class ParticipantViewQuery
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
     * @var SlotView[]
     */
    public $slots;

    /**
     * @param Event       $event
     * @param SheetView[] $sheets
     * @param SlotView[]  $slots
     */
    public function __construct(Event $event, array $sheets, array $slots)
    {
        $this->event  = $event;
        $this->sheets = $sheets;
        $this->slots  = $slots;
    }
}
