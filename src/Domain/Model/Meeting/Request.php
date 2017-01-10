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
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class Request implements MessageSubjectInterface
{
    const STATE_SENT     = 'sent';
    const STATE_APPROVED = 'approved';
    const STATE_REFUSED  = 'refused';

    /**
     * @var int
     */
    private $id;

    /**
     * @var Sheet
     */
    private $from;

    /**
     * @var ArrayCollection
     */
    private $fromParticipants;

    /**
     * @var Sheet
     */
    private $to;

    /**
     * @var ArrayCollection
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
     * @var \DateTimeInterface
     */
    private $stateUpdatedAt;

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
        $this->stateUpdatedAt   = $createdAt;
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
     * @return \DateTimeInterface
     */
    public function getStateUpdatedAt()
    {
        return $this->stateUpdatedAt;
    }

    /**
     * @param \DateTimeInterface $date
     *
     * @return Request
     */
    public function refuse(\DateTimeInterface $date)
    {
        $this->state          = self::STATE_REFUSED;
        $this->stateUpdatedAt = $date;

        return $this;
    }

    /**
     * @param \DateTimeInterface $date
     *
     * @return Request
     */
    public function approve(\DateTimeInterface $date)
    {
        $this->state          = self::STATE_APPROVED;
        $this->stateUpdatedAt = $date;

        return $this;
    }

    /**
     * @param DateTimeInterface $date
     */
    public function unRefuse(\DateTimeInterface $date)
    {
        $this->state          = self::STATE_SENT;
        $this->stateUpdatedAt = $date;
    }

    /**
     * @param DateTimeInterface $date
     */
    public function unApprove(\DateTimeInterface $date)
    {
        $this->state          = self::STATE_SENT;
        $this->stateUpdatedAt = $date;
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
        return $this->toParticipants->contains($participant);
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

    /**
     * @return bool
     */
    public function isSent()
    {
        return self::STATE_SENT === $this->state;
    }

    /**
     * @return bool
     */
    public function isApproved()
    {
        return self::STATE_APPROVED === $this->state;
    }

    /**
     * @return bool
     */
    public function isRefused()
    {
        return self::STATE_REFUSED === $this->state;
    }

    /**
     * @return array
     */
    public static function getAllStates()
    {
        return [
            self::STATE_SENT,
            self::STATE_APPROVED,
            self::STATE_REFUSED,
        ];
    }

    /**
     * @param Sheet $sheet
     *
     * @return bool
     */
    public function isSender(Sheet $sheet)
    {
        return $this->from === $sheet;
    }

    /**
     * @param Sheet $sheet
     *
     * @return bool
     */
    public function isReceiver(Sheet $sheet)
    {
        return $this->to === $sheet;
    }

    /**
     * @param Sheet $sheet
     *
     * @return bool
     */
    public function hasNoPreference(Sheet $sheet)
    {
        if ($this->from === $sheet) {
            return $this->fromParticipants->isEmpty();
        }

        if ($this->to === $sheet) {
            return $this->toParticipants->isEmpty();
        }

        throw new \InvalidArgumentException('Sheet not concerned by this meeting request');
    }
}
