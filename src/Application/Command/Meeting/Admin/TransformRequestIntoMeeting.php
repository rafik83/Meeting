<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting\Admin;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;

class TransformRequestIntoMeeting
{
    /** @var Meeting\Request */
    public $meetingRequest;

    /** @var MeetingSlot */
    public $slot;

    /** @var Event */
    public $event;

    /** @var bool */
    public $visio;

    /**
     * @param Meeting\Request $meetingRequest
     * @param MeetingSlot     $slot
     * @param bool            $visio
     */
    public function __construct(
        Meeting\Request $meetingRequest,
        MeetingSlot $slot,
        $visio = false
    ) {
        $this->meetingRequest = $meetingRequest;
        $this->slot           = $slot;
        $this->event          = $slot->getEvent();
        $this->visio          = $visio;
    }
}
