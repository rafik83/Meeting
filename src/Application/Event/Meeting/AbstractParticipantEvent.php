<?php

namespace Proximum\Vimeet\Application\Event\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\EventDispatcher\Event;

abstract class AbstractParticipantEvent extends Event
{
    /**
     * @var User
     */
    private $emitter;

    /**
     * @var Participant
     */
    private $participant;

    /**
     * @var Meeting
     */
    private $meeting;

    /**
     * @var string
     */
    private $message;

    /**
     * @var \DateTimeInterface
     */
    private $date;

    /**
     * ParticipantAddedEvent constructor.
     *
     * @param User               $emitter
     * @param Participant        $participant
     * @param Meeting            $meeting
     * @param string             $message
     * @param \DateTimeInterface $date
     */
    public function __construct(User $emitter, Participant $participant, Meeting $meeting, $message, \DateTimeInterface $date)
    {
        $this->emitter     = $emitter;
        $this->participant = $participant;
        $this->meeting     = $meeting;
        $this->message     = $message;
        $this->date        = $date;
    }

    /**
     * Get emitter
     *
     * @return User
     */
    public function getEmitter()
    {
        return $this->emitter;
    }

    /**
     * Get participant
     *
     * @return Participant
     */
    public function getParticipant()
    {
        return $this->participant;
    }

    /**
     * Get meeting
     *
     * @return Meeting
     */
    public function getMeeting()
    {
        return $this->meeting;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Get date
     *
     * @return \DateTimeInterface
     */
    public function getDate()
    {
        return $this->date;
    }
}
