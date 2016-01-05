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
use Proximum\Vimeet\Domain\Model\Meeting\MessageSubjectInterface;

class Meeting implements MessageSubjectInterface
{
    const STATE_SCHEDULED = 'scheduled';
    const STATE_CANCELED  = 'canceled';

    /**
     * @var int
     */
    private $id;

    /**
     * @var MeetingSlot
     */
    private $slot;

    /**
     * @var Sheet
     */
    private $fromSheet;

    /**
     * @var ArrayCollection
     */
    private $fromParticipants;

    /**
     * @var Sheet
     */
    private $toSheet;

    /**
     * @var ArrayCollection
     */
    private $toParticipants;

    /**
     * @var \DateTimeInterface
     */
    private $createdAt;

    /**
     * @var string
     */
    private $state = self::STATE_SCHEDULED;

    /**
     * Meeting constructor.
     *
     * @param MeetingSlot        $slot
     * @param Sheet              $fromSheet
     * @param array              $fromParticipants
     * @param Sheet              $toSheet
     * @param array              $toParticipants
     * @param \DateTimeInterface $createdAt
     */
    public function __construct(
        MeetingSlot $slot,
        Sheet $fromSheet,
        array $fromParticipants,
        Sheet $toSheet,
        array $toParticipants,
        \DateTimeInterface $createdAt
    ) {
        $this->slot             = $slot;
        $this->fromSheet        = $fromSheet;
        $this->fromParticipants = new ArrayCollection($fromParticipants);
        $this->toSheet          = $toSheet;
        $this->toParticipants   = new ArrayCollection($toParticipants);
        $this->createdAt        = $createdAt;
    }

    /**
     * Get id
     *
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
        return $this->fromSheet;
    }

    /**
     * @return Participant[]
     */
    public function getFromParticipants()
    {
        return $this->fromParticipants->toArray();
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
    public function getToSheet()
    {
        return $this->toSheet;
    }

    /**
     * @return Participant[]
     */
    public function getToParticipants()
    {
        return $this->toParticipants->toArray();
    }

    /**
     * Get slot
     *
     * @return MeetingSlot
     */
    public function getSlot()
    {
        return $this->slot;
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
     * @return \DateTimeInterface
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
     * @return Meeting
     */
    public function cancel()
    {
        $this->state = self::STATE_CANCELED;

        return $this;
    }
}
