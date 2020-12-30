<?php

namespace Proximum\Vimeet\Domain\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Participant\IsParticipantVisio;

class VisioGuesser
{
    /** @var IsParticipantVisio */
    private $isParticipantVisio;

    /** @var MeetingParticipants */
    private $meetingParticipants;

    public function __construct(IsParticipantVisio $isParticipantVisio, MeetingParticipants $meetingParticipants)
    {
        $this->isParticipantVisio = $isParticipantVisio;
        $this->meetingParticipants = $meetingParticipants;
    }

    public function hasMeetingParticipantVisio(Meeting $meeting): bool
    {
        $participants = $meeting->getAllParticipants();

        return $this->isParticipantVisio($participants);
    }

    public function hasMeetingRequestParticipantVisio(Request $request): bool
    {
        $participants = $this->meetingParticipants->getAllMeetingParticipants($request);

        return $this->isParticipantVisio($participants);
    }

    /**
     * @param Participant[] $participants
     *
     * @return bool
     */
    public function isParticipantVisio(array $participants): bool
    {
        foreach ($participants as $participant) {
            if ($this->isParticipantVisio->isSatisfiedBy($participant)) {
                return true;
            }
        }

        return false;
    }
}
