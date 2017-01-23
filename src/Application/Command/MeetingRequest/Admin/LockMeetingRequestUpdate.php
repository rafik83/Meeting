<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\MeetingRequest\Admin;

use Proximum\Vimeet\Domain\Model\Event;

class LockMeetingRequestUpdate
{
    /**
     * @var bool
     */
    public $lock;

    /**
     * @var Event
     */
    public $event;

    /**
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->lock  = $event->getConfiguration()->isMeetingRequestUpdateLocked();
    }
}
