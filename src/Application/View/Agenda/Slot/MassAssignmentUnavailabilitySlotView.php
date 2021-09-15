<?php

namespace Proximum\Vimeet\Application\View\Agenda\Slot;

use Proximum\Vimeet\Domain\Model\MeetingSlot;

class MassAssignmentUnavailabilitySlotView extends AbstractSlotView
{
    /**
     * @var int
     */
    public $massAssignmentId;

    /**
     * MassUnavailabilitySlotView constructor.
     *
     * @param MeetingSlot $slot
     * @param string      $type
     * @param int         $massAssignmentId
     */
    public function __construct(MeetingSlot $slot, $type, $massAssignmentId)
    {
        parent::__construct($slot, $type);

        $this->massAssignmentId = $massAssignmentId;
    }
}
