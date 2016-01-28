<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Meeting;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\User;

class Request implements MessageSubjectInterface
{
    const STATE_SENT     = 'sent';
    const STATE_APPROVED = 'approved';
    const STATE_REFUSED  = 'refused';
    const STATE_CANCEL   = 'cancelled';

    /**
     * @var int
     */
    private $id;

    /**
     * @var Sheet
     */
    private $from;

    /**
     * @var Participant[]
     */
    private $fromParticipants;

    /**
     * @var Sheet
     */
    private $to;

    /**
     * @var Participant[]
     */
    private $toParticipants;

    /**
     * @var string
     */
    private $state;

    /**
     * @var \DateTimeInterface
     */
    private $createdAt;

    /**
     * @var MeetingSlot
     */
    private $meetingSlot;

    /**
     * @var Meeting
     */
    private $meeting;

    /**
     * @var User
     */
    private $creator;

    /**
     * Request constructor.
     *
     * @param Sheet              $from
     * @param array              $fromParticipants
     * @param Sheet              $to
     * @param array              $toParticipants
     * @param \DateTimeInterface $createdAt
     * @param User               $creator
     */
    public function __construct(
        Sheet $from,
        array $fromParticipants,
        Sheet $to,
        array $toParticipants,
        DateTimeInterface $createdAt,
        User $creator
    ) {
        $this->from             = $from;
        $this->fromParticipants = new ArrayCollection($fromParticipants);
        $this->to               = $to;
        $this->toParticipants   = new ArrayCollection($toParticipants);
        $this->state            = self::STATE_SENT;
        $this->createdAt        = $createdAt;
        $this->creator          = $creator;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Sheet
     */
    public function getFromSheet()
    {
        return $this->from;
    }

    /**
     * @return ArrayCollection
     */
    public function getFromParticipants()
    {
        return $this->fromParticipants;
    }

    /**
     * @return Sheet
     */
    public function getToSheet()
    {
        return $this->to;
    }

    /**
     * @return ArrayCollection
     */
    public function getToParticipants()
    {
        return $this->toParticipants;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return Request
     */
    public function refuse()
    {
        $this->state = self::STATE_REFUSED;

        return $this;
    }

    /**
     * @return Request
     */
    public function cancel()
    {
        $this->state = self::STATE_CANCEL;

        return $this;
    }

    /**
     * @deprecated
     *
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @param Participant $participant
     *
     * @return Request
     */
    public function addToParticipant(Participant $participant)
    {
        $this->toParticipants[] = $participant;

        return $this;
    }

    /**
     * Get meetingSlot
     *
     * @return MeetingSlot
     */
    public function getMeetingSlot()
    {
        return $this->meetingSlot;
    }

    /**
     * Set meetingSlot
     *
     * @param MeetingSlot $meetingSlot
     *
     * @return Request
     */
    public function setMeetingSlot($meetingSlot)
    {
        $this->meetingSlot = $meetingSlot;

        return $this;
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
     * Set meeting
     *
     * @param Meeting $meeting
     *
     * @return Request
     */
    public function setMeeting($meeting)
    {
        $this->meeting = $meeting;

        return $this;
    }

    /**
     * @return User
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @return bool
     */
    public function hasToParticipants()
    {
        return !$this->toParticipants->isEmpty();
    }

    /**
     * @return bool
     */
    public function hasFromParticipants()
    {
        return !$this->fromParticipants->isEmpty();
    }

    /**
     * @param Participant $participant
     *
     * @return bool
     */
    public function hasFromParticipant(Participant $participant)
    {
        return $this->fromParticipants->contains($participant);
    }

    /**
     * @param Participant $participant
     *
     * @return bool
     */
    public function hasToParticipant(Participant $participant)
    {
        return $this->fromParticipants->contains($participant);
    }

    /**
     * @param Participant $fromParticipant
     *
     * @return Request
     */
    public function addFromParticipant(Participant $fromParticipant)
    {
        $this->fromParticipants->add($fromParticipant);

        return $this;
    }

    /**
     * @param Participant $participant
     *
     * @return Request
     */
    public function removeToParticipant(Participant $participant)
    {
        $this->toParticipants->removeElement($participant);

        return $this;
    }

    /**
     * @param Participant $participant
     *
     * @return Request
     */
    public function removeFromParticipant(Participant $participant)
    {
        $this->fromParticipants->removeElement($participant);

        return $this;
    }
}
