<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
