<?php

namespace Proximum\Vimeet\Application\Query\MeetingSlot;

use Proximum\Vimeet\Domain\Model\MeetingSlot;

class GetAvailableSlotsView
{
    /** @var MeetingSlot[] */
    public $availableSlots;

    public function __construct(array $availableSlots)
    {
        $this->availableSlots = $availableSlots;
    }
}
