<?php

namespace Proximum\Vimeet\Application\Event\MeetingRequest;

use Proximum\Vimeet\Domain\Model\Participant;
use Symfony\Component\EventDispatcher\Event;

abstract class AbstractParticipantAssignedEvent extends Event
{
    /** @var Participant */
    public $participant;

    /**
     * @param Participant $participant
     */
    public function __construct(Participant $participant)
    {
        $this->participant = $participant;
    }
}
