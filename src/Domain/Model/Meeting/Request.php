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
     * @var DateTime
     */
    private $createdAt;

    /**
     * @var Notification[]
     */
    private $notifications;

    /**
     * @param Sheet             $from
     * @param array             $fromParticipants
     * @param Sheet             $to
     * @param string            $description
     * @param DateTimeInterface $createdAt
     */
    public function __construct(
        Sheet $from,
        array $fromParticipants,
        Sheet $to,
        $description,
        DateTimeInterface $createdAt
    ) {
        $this->from             = $from;
        $this->fromParticipants = $fromParticipants;
        $this->to               = $to;
        $this->description      = $description;
        $this->state            = self::STATE_SENT;
        $this->createdAt        = $createdAt;
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
     * @param Participant[] $toParticipants
     */
    public function setToParticipants($toParticipants)
    {
        $this->toParticipants = $toParticipants;
    }

    /**
     * @param Participant[] $fromParticipants
     */
    public function setFromParticipants($fromParticipants)
    {
        $this->fromParticipants = $fromParticipants;
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
}
