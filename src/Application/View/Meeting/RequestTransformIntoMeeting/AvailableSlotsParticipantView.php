<?php

namespace Proximum\Vimeet\Application\View\Meeting\RequestTransformIntoMeeting;

use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;

class AvailableSlotsParticipantView
{
    /**
     * @var Participant
     */
    public $participant;

    /**
     * @var MeetingSlot[]
     */
    public $slots = [];

    /**
     * @param Participant   $participant
     * @param MeetingSlot[] $slots
     */
    public function __construct(Participant $participant, array $slots)
    {
        $this->participant = $participant;
        $this->slots       = $slots;
    }
}
