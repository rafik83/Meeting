<?php

namespace Proximum\Vimeet\Application\Command\MeetingSlot;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\MeetingSlot;

class Unlock implements Command
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
