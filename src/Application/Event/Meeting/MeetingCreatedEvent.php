<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting;
use Symfony\Component\EventDispatcher\Event;

class MeetingCreatedEvent extends Event
{
    /**
     * @var Meeting
     */
    private $meeting;

    /**
     * @param Meeting $meeting
     */
    public function __construct(Meeting $meeting)
    {
        $this->meeting = $meeting;
    }

    /**
     * @return Meeting
     */
    public function getMeeting(): Meeting
    {
        return $this->meeting;
    }
}
