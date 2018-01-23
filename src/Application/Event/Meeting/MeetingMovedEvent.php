<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\EventDispatcher\Event;

class MeetingMovedEvent extends Event
{
    /** @var Meeting */
    private $meeting;

    /**
     * @param Meeting $meeting
     */
    public function __construct(Meeting $meeting)
    {
        $this->meeting = $meeting;
    }

    /**
     * @return Sheet[]
     */
    public function getSheets(): array
    {
        return [$this->meeting->getFromSheet(), $this->meeting->getToSheet()];
    }
}
