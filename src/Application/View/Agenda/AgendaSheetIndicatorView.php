<?php

/*
 * This file is part of the vimeet project.
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

    /**
     * AgendaSheetIndicatorView constructor.
     *
     * @param int $maxMeetingAvailable
     */
    public function __construct($maxMeetingAvailable)
    {
        $this->maxMeetingAvailable = $maxMeetingAvailable;
    }
}
