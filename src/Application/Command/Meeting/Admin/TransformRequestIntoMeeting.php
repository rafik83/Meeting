<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting\Admin;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;

class TransformRequestIntoMeeting
{
    /** @var Meeting\Request */
    public $meetingRequest;

    /** @var MeetingSlot */
    public $slot;

    /**
     * @param Meeting\Request $meetingRequest
     * @param MeetingSlot     $slot
     */
    public function __construct(Meeting\Request $meetingRequest, MeetingSlot $slot)
    {
        $this->meetingRequest = $meetingRequest;
        $this->slot           = $slot;
    }
}
