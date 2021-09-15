<?php

namespace Proximum\Vimeet\Domain\Model\Sheet;

use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Sheet;

class AvailableSlot
{
    /** @var Sheet */
    private $sheet;

    /** @var MeetingSlot */
    private $slot;

    /**
     * @param Sheet       $sheet
     * @param MeetingSlot $slot
     */
    public function __construct(Sheet $sheet, MeetingSlot $slot)
    {
        $this->sheet = $sheet;
        $this->slot = $slot;
    }

    /**
     * @return Sheet
     */
    public function getSheet(): Sheet
    {
        return $this->sheet;
    }

    /**
     * @return MeetingSlot
     */
    public function getSlot(): MeetingSlot
    {
        return $this->slot;
    }
}
