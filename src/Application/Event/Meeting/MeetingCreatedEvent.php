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
use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\EventDispatcher\Event;

class MeetingCreatedEvent extends Event
{
    /**
     * @var Sheet
     */
    private $sheets;

    /**
     * @var Meeting
     */
    private $meeting;

    /**
     * @param Meeting $meeting
     * @param Sheet[] $sheets
     */
    public function __construct(Meeting $meeting, array $sheets)
    {
        $this->sheets  = $sheets;
        $this->meeting = $meeting;
    }

    /**
     * @return Sheet[]
     */
    public function getSheets()
    {
        return $this->sheets;
    }

    /**
     * @return Meeting
     */
    public function getMeeting(): Meeting
    {
        return $this->meeting;
    }
}
