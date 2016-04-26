<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\MeetingRequest;

use Proximum\Vimeet\Domain\Model\Event;

class RequestsToMeetings
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var \DateTime
     */
    public $createdAt;

    /**
     * @param Event              $event
     * @param \DateTimeInterface $createdAt
     */
    public function __construct(Event $event, \DateTimeInterface $createdAt)
    {
        $this->event     = $event;
        $this->createdAt = $createdAt;
    }
}
