<?php

namespace Proximum\Vimeet\Application\Query\Agenda\AvailableSheets;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetsAvailableBySlotQuery
{
    /** @var Event */
    public $event;

    /** @var Sheet */
    public $sheet;

    /** @var MeetingSlot */
    public $slot;

    /**
     * @param Event       $event
     * @param Sheet       $sheet
     * @param MeetingSlot $slot
     */
    public function __construct(
        Event $event,
        Sheet $sheet,
        MeetingSlot $slot
    ) {
        $this->event = $event;
        $this->sheet = $sheet;
        $this->slot = $slot;
    }
}
