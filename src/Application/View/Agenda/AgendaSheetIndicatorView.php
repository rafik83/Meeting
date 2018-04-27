<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda;

class AgendaSheetIndicatorView
{
    /** @var int */
    public $maxMeetingAvailable;

    /** @var int */
    public $countPlacedMeeting;

    /**
     * AgendaSheetIndicatorView constructor.
     *
     * @param int $maxMeetingAvailable
     * @param int $countPlacedMeeting
     */
    public function __construct($maxMeetingAvailable, $countPlacedMeeting)
    {
        $this->maxMeetingAvailable = $maxMeetingAvailable;
        $this->countPlacedMeeting  = $countPlacedMeeting;
    }
}
