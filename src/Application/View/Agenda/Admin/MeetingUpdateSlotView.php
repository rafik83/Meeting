<?php

namespace Proximum\Vimeet\Application\View\Agenda\Admin;

class MeetingUpdateSlotView
{
    /** @var int[] */
    public $availableSlotsId;

    /**
     * @param int[] $availableSlotsId
     */
    public function __construct(array $availableSlotsId)
    {
        $this->availableSlotsId = $availableSlotsId;
    }
}
