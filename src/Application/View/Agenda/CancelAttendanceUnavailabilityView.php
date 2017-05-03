<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda;

class CancelAttendanceUnavailabilityView extends AbstractTimeEntityView
{
    /**
     * @var string
     */
    public $timeZone;

    /**
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     * @param string             $timeZone
     */
    public function __construct(
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        $timeZone
    ) {
        $this->begin    = $begin;
        $this->end      = $end;
        $this->timeZone = $timeZone;
    }
}
