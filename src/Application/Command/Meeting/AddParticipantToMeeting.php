<?php

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;

class AddParticipantToMeeting implements Command
{
    /** @var Participant */
    public $participant;

    /** @var Meeting */
    public $meeting;

    public function __construct(Participant $participant, Meeting $meeting)
    {
        $this->participant = $participant;
        $this->meeting = $meeting;
    }
}
