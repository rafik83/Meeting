<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\MeetingRequest;

use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Sheet;

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
     * @var \DateTimeInterface
     */
    public $createdAt;

    /**
     * PositionMeeting constructor.
     *
     * @param Request            $request
     * @param \DateTimeInterface $createdAt
     */
    public function __construct(Request $request, \DateTimeInterface $createdAt)
    {
        $this->meetingRequest   = $request;
        $this->fromSheet        = $request->getFromSheet();
        $this->fromParticipants = $request->getFromParticipants()->toArray();
        $this->toSheet          = $request->getToSheet();
        $this->toParticipants   = $request->getToParticipants()->toArray();
        $this->createdAt        = $createdAt;
    }
}
