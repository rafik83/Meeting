<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;

class Update
{
    /**
     * @var Meeting
     */
    public $meeting;

    /**
     * @var MeetingSlot
     */
    public $meetingSlot;

    /**
     * @var array
     */
    public $fromParticipants;

    /**
     * @var array
     */
    public $toParticipants;

    /**
     * @var string
     */
    public $message;

    /**
     * Update constructor.
     *
     * @param Meeting $meeting
     */
    public function __construct(Meeting $meeting)
    {
        $this->meeting          = $meeting;
        $this->meetingSlot      = $meeting->getMeetingSlot();
        $this->fromParticipants = $meeting->getFromParticipants();
        $this->toParticipants   = $meeting->getToParticipants();
    }
}
