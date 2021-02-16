<?php

namespace Proximum\Vimeet\Domain\Meeting\Slot;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Unavailability\MassAssignment;

class SlotAvailabilityView
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var Meeting|null
     */
    public $meeting;

    /**
     * @var MassAssignment|null
     */
    public $massAssignment;

    /**
     * @var Sheet|null
     */
    public $otherSheet;

    /**
     * SlotAvailabilityView constructor.
     *
     * @param string              $type
     * @param Meeting|null        $meeting
     * @param MassAssignment|null $massAssignment
     * @param Sheet|null          $otherSheet
     */
    public function __construct(
        $type,
        Meeting $meeting = null,
        MassAssignment $massAssignment = null,
        Sheet $otherSheet = null
    ) {
        $this->type           = $type;
        $this->meeting        = $meeting;
        $this->massAssignment = $massAssignment;
        $this->otherSheet     = $otherSheet;
    }

    /**
     * @return bool
     */
    public function isAvailable()
    {
        return SlotAvailability::SLOT_AVAILABLE === $this->type;
    }

    /**
     * @return bool
     */
    public function isMeeting()
    {
        return SlotAvailability::MEETING_UNAVAILABILITY === $this->type;
    }

    /**
     * @return bool
     */
    public function isMassUnavaibility()
    {
        return SlotAvailability::MASS_UNAVAILABILITY === $this->type
            || SlotAvailability::MASS_ASSIGNMENT_UNAVAILABILITY === $this->type;
    }
}
