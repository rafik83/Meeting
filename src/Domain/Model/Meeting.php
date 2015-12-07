<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;

class Meeting
{
    const STATE_SCHEDULED = 'scheduled';
    const STATE_CANCELED  = 'canceled';

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
     * @var MeetingSlot
     */
    private $meetingSlot;

    /**
     * @var DateTime
     */
    private $createdAt;

    /**
     * @var string
     */
    private $state = self::STATE_SCHEDULED;

    /**
     * Meeting constructor.
     */
    public function __construct()
    {
        $this->fromParticipants = new ArrayCollection();
        $this->toParticipants   = new ArrayCollection();
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
    public function getFrom()
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
     * @param Participant $participant
     *
     * @return Meeting
     */
    public function addFromParticipant(Participant $participant)
    {
        $this->fromParticipants[$participant->getId()] = $participant;

        return $this;
    }

    /**
     * @param Participant $participant
     *
     * @return Meeting
     */
    public function removeFromParticipant(Participant $participant)
    {
        $this->fromParticipants->removeElement($participant);

        return $this;
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
     * @return Sheet
     */
    public function getTo()
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
     * @param Participant $participant
     *
     * @return Meeting
     */
    public function addToParticipant(Participant $participant)
    {
        $this->toParticipants[] = $participant;

        return $this;
    }

    /**
     * @param Participant $participant
     *
     * @return Meeting
     */
    public function removeToParticipant(Participant $participant)
    {
        $this->toParticipants->removeElement($participant);

        return $this;
    }

    /**
     * @param Participant $participant
     *
     * @return bool
     */
    public function hasToParticipant(Participant $participant)
    {
        return $this->toParticipants->contains($participant);
    }

    /**
     * @return MeetingSlot
     */
    public function getMeetingSlot()
    {
        return $this->meetingSlot;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Get state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return MeetingSlot
     */
    public function cancel()
    {
        $this->state = self::STATE_CANCELED;

        return $this;
    }
}
