<?php

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Application\View\Agenda\CancelAttendanceUnavailabilityView;

class CancelAttendanceUnavailabilityViewQueryHandler
{
    public function handle(CancelAttendanceUnavailabilityViewQuery $query): CancelAttendanceUnavailabilityView
    {
        return new CancelAttendanceUnavailabilityView(
            $query->day->getBegin(),
            $query->day->getEnd(),
            $query->event->getTimeZone()
        );
    }
}
