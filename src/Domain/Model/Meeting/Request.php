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
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;

class Request
{
    const STATE_SENT = 'sent';
    const STATE_APPROVED = 'approved';
    const STATE_REFUSED = 'refused';

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
     * @var string
     */
    private $refuseMessage;

    /**
     * @param Sheet    $from
     * @param array    $fromParticipants
     * @param Sheet    $to
     * @param string   $description
     * @param DateTime $createdAt
     */
    public function __construct(Sheet $from, array $fromParticipants, Sheet $to, $description, DateTime $createdAt)
    {
        $this->from = $from;
        $this->fromParticipants = $fromParticipants;
        $this->to = $to;
        $this->description = $description;
        $this->state = self::STATE_SENT;
        $this->createdAt = $createdAt;
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
     * @return DateTime
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
     * @return string
     */
    public function getRefuseMessage()
    {
        return $this->refuseMessage;
    }

    /**
     * @param string $refuseMessage
     */
    public function setRefuseMessage($refuseMessage)
    {
        $this->refuseMessage = $refuseMessage;
    }
}
