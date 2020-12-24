<?php

namespace Proximum\Vimeet\Application\Query\Meeting\Admin\ListMeeting;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;

class ParticipantViewQuery
{
    /** @var Participant */
    public $participant;

    /** @var Event */
    public $event;

    /** @var Meeting */
    public $meeting;

    /** @var MeetingSlot */
    public $meetingSlot;

    /** @var string */
    public $locale;

    public function __construct(
        Participant $participant,
        Event $event,
        Meeting $meeting,
        MeetingSlot $meetingSlot,
        string $locale
    ) {
        $this->participant = $participant;
        $this->event = $event;
        $this->meeting = $meeting;
        $this->meetingSlot = $meetingSlot;
        $this->locale = $locale;
    }
}
