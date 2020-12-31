<?php

namespace Proximum\Vimeet\Application\Event\Participant;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\EventDispatcher\Event;

class ParticipantAddedEvent extends Event
{
    /** @var Participant */
    public $participant;

    /** @var User */
    public $adderOfTheParticipant;

    /**
     * @param Participant $participant
     * @param User        $adderOfTheParticipant
     */
    public function __construct(Participant $participant, User $adderOfTheParticipant)
    {
        $this->participant = $participant;
        $this->adderOfTheParticipant = $adderOfTheParticipant;
    }
}
