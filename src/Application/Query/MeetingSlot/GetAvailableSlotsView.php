<?php

namespace Proximum\Vimeet\Application\Query\MeetingSlot;

use Proximum\Vimeet\Domain\Model\MeetingSlot;

class GetAvailableSlotsView
{
    /** @var MeetingSlot[] available slots for met sheet participants */
    public array $availableSlots;
    /** @var int[] available slots ids, for current sheet participants, indexed by participant id */
    public array $currentSheetAvailableSlotIds;

    public function __construct(array $availableSlots, array $currentSheetAvailableSlotIds)
    {
        $this->availableSlots = $availableSlots;
        $this->currentSheetAvailableSlotIds = $currentSheetAvailableSlotIds;
    }
}
