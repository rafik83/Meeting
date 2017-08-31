<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda\AvailableSheets;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetsAvailableBySlotQuery
{
    /** @var Event */
    public $event;

    /** @var Sheet */
    public $excludedSheet;

    /** @var MeetingSlot */
    public $slot;

    /**
     * @param Event       $event
     * @param Sheet       $excludedSheet
     * @param MeetingSlot $slot
     */
    public function __construct(
        Event $event,
        Sheet $excludedSheet,
        MeetingSlot $slot
    ) {
        $this->event = $event;
        $this->excludedSheet = $excludedSheet;
        $this->slot = $slot;
    }
}
