<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Meeting;

use DateTime;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;

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
