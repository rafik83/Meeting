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
use Proximum\Vimeet\Domain\Model\Notification;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\User;

class Request
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
     * @var string
     */
    private $description;

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
     * @var DateTimeInterface
     */
    private $createdAt;

    /**
     * @var Notification[]
     */
    private $notifications;

    /**
     * @var Meeting
     */
    private $meeting;

    /**
     * @var User
     */
    private $creator;

    /**
     * @param Sheet             $from
     * @param array             $fromParticipants
     * @param Sheet             $to
     * @param array             $toParticipants
     * @param string            $description
     * @param DateTimeInterface $createdAt
     * @param User              $creator
     */
    public function __construct(
        Sheet $from,
        array $fromParticipants,
        Sheet $to,
        array $toParticipants,
        $description,
        DateTimeInterface $createdAt,
        User $creator
    ) {
        $this->from             = $from;
        $this->fromParticipants = new ArrayCollection($fromParticipants);
        $this->to               = $to;
        $this->toParticipants   = new ArrayCollection($toParticipants);
        $this->description      = $description;
        $this->state            = self::STATE_SENT;
        $this->createdAt        = $createdAt;
        $this->creator          = $creator;
        $this->notifications    = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return Sheet
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return Participant[]
     */
    public function getFromParticipants()
    {
        return $this->fromParticipants;
    }

    /**
     * @return Sheet
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @return Participant[]
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
     * @return DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
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
     * @param Participant $fromParticipant
     */
    public function addFromParticipant(Participant $fromParticipant)
    {
        $this->fromParticipants->add($fromParticipant);
    }

    /**
     * @return Notification[]
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * @param Notification $notification
     */
    public function addNotifications(Notification $notification)
    {
        $this->notifications[] = $notification;
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
     * Get meeting
     *
     * @return null|Meeting
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
}
