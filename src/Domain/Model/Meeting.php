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
     * Get slot
     *
     * @return MeetingSlot
     */
    public function getSlot()
    {
        return $this->slot;
    }

    /**
     * Get fromSheet
     *
     * @return Sheet
     */
    public function getFromSheet()
    {
        return $this->fromSheet;
    }

    /**
     * Get fromParticipants
     *
     * @return ArrayCollection
     */
    public function getFromParticipants()
    {
        return $this->fromParticipants;
    }

    /**
     * Get toSheet
     *
     * @return Sheet
     */
    public function getToSheet()
    {
        return $this->toSheet;
    }

    /**
     * Get toParticipants
     *
     * @return ArrayCollection
     */
    public function getToParticipants()
    {
        return $this->toParticipants;
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
     * @return MeetingSlot
     */
    public function cancel()
    {
        $this->state = self::STATE_CANCELED;

        return $this;
    }
}
