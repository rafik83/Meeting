<?php

namespace Proximum\Vimeet\Application\Command\Meeting\Admin;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;

class TransformRequestIntoMeeting implements Command
{
    /** @var Meeting\Request */
    public $meetingRequest;

    /** @var MeetingSlot */
    public $slot;

    /** @var Event */
    public $event;

    /** @var bool */
    public $visio;

    /**
     * @param Meeting\Request $meetingRequest
     * @param MeetingSlot     $slot
     * @param bool            $visio
     */
    public function __construct(
        Meeting\Request $meetingRequest,
        MeetingSlot $slot,
        $visio = false
    ) {
        $this->meetingRequest = $meetingRequest;
        $this->slot           = $slot;
        $this->event          = $slot->getEvent();
        $this->visio          = $visio;
    }
}
