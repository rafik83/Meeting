<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Domain\Meeting\Slot\SlotAvailability;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetsAvailableBySlotQuery
{
    /** @var Event */
    public $event;

    /** @var Sheet */
    public $excludedSheet;

    /** @var SlotAvailability */
    public $slot;

    /**
     * @param Event            $event
     * @param Sheet            $excludedSheet
     * @param SlotAvailability $slot
     */
    public function __construct(
        Event $event,
        Sheet $excludedSheet,
        SlotAvailability $slot
    ) {
        $this->event = $event;
        $this->excludedSheet = $excludedSheet;
        $this->slot = $slot;
    }
}
