<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Spot;

class VideoConferenceViewQuery
{
    /**
     * @var Meeting
     */
    public $meeting;

    /**
     * @var MeetingSlot
     */
    public $slot;

    /**
     * @var Spot
     */
    public $spot;

    /**
     * VideoConferenceViewQuery constructor.
     *
     * @param Meeting     $meeting
     * @param MeetingSlot $slot
     * @param Spot        $spot
     */
    public function __construct(Meeting $meeting, MeetingSlot $slot, Spot $spot)
    {
        $this->meeting = $meeting;
        $this->slot    = $slot;
        $this->spot    = $spot;
    }
}
