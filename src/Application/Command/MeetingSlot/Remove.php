<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\MeetingSlot;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Sheet;

class Remove implements Command
{
    /** @var MeetingSlot */
    public $meetingSlot;

    /** @var Sheet */
    public $sheet;

    /** @var Meeting */
    public $meeting;

    /** @var string */
    public $content;

    /**
     * Remove constructor.
     *
     * @param MeetingSlot $meetingSlot
     * @param Sheet       $sheet
     * @param Meeting     $meeting
     */
    public function __construct(MeetingSlot $meetingSlot, Sheet $sheet = null, Meeting $meeting = null)
    {
        $this->meetingSlot = $meetingSlot;
        $this->sheet = $sheet;
        $this->meeting = $meeting;
    }
}
