<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\MeetingSlot;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;

class Lock
{
    /**
     * @var MeetingSlot
     */
    public $meetingSlot;

    /**
     * @var Event
     */
    public $event;

    /**
     * Lock constructor.
     *
     * @param MeetingSlot $meetingSlot
     * @param Event       $event
     */
    public function __construct(MeetingSlot $meetingSlot, Event $event)
    {
        $this->meetingSlot = $meetingSlot;
        $this->event       = $event;
    }
}
