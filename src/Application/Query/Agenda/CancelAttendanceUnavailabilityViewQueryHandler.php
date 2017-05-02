<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Application\View\Agenda\CancelAttendanceUnavailabilityView;
use Proximum\Vimeet\Application\View\Agenda\UnavailabilityView;

class CancelAttendanceUnavailabilityViewQueryHandler
{
    /**
     * @param CancelAttendanceUnavailabilityViewQuery $query
     *
     * @return UnavailabilityView
     */
    public function handle(CancelAttendanceUnavailabilityViewQuery $query)
    {
        return new CancelAttendanceUnavailabilityView(
            $query->day->getStartTime(),
            $query->day->getEndTime(),
            $query->event->getTimeZone()
        );
    }
}
