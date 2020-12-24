<?php

namespace Proximum\Vimeet\Application\Command\MeetingSlot;

use Proximum\Vimeet\Domain\Model\MeetingSlot;

class Unlock
{
    /**
     * @var MeetingSlot
     */
    public $meetingSlot;

    /**
     * Lock constructor.
     *
     * @param MeetingSlot $meetingSlot
     */
    public function __construct(MeetingSlot $meetingSlot)
    {
        $this->meetingSlot = $meetingSlot;
    }
}
