<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\MeetingRequest;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Meeting\Request;

class PositionMeeting
{
    /**
     * @var Request
     */
    public $meetingRequest;

    /**
     * @var Sheet
     */
    public $fromSheet;

    /**
     * @var array
     */
    public $fromParticipants;

    /**
     * @var Sheet
     */
    public $toSheet;

    /**
     * @var array
     */
    public $toParticipants;

    /**
     * @var MeetingSlot
     */
    public $slot;

    /**
     * @var \DateTime
     */
    public $createdAt;

    /**
     * PositionMeeting constructor.
     *
     * @param Request   $request
     * @param \DateTime $createdAt
     */
    public function __construct(Request $request, \DateTime $createdAt)
    {
        $this->meetingRequest   = $request;
        $this->fromSheet        = $request->getFrom();
        $this->fromParticipants = $request->getFromParticipants()->toArray();
        $this->toSheet          = $request->getTo();
        $this->toParticipants   = $request->getFromParticipants()->toArray();
        $this->createdAt        = $createdAt;
    }
}
