<?php

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Application\View\Agenda\UnavailabilityView;
use Proximum\Vimeet\Domain\Time\TimeOverlap;

class UnavailabilityViewQueryHandler
{
    public function handle(UnavailabilityViewQuery $query): UnavailabilityView
    {
        $begin = TimeOverlap::ceil($query->day->getBegin(), $query->unavailability->getBegin());
        $end = TimeOverlap::floor($query->day->getEnd(), $query->unavailability->getEnd());

        return new UnavailabilityView(
            $query->unavailability->getId(),
            $begin,
            $end,
            $query->event->getTimeZone(),
            $query->unavailability->getMessage(),
            $query->unavailability->isCreatedByUser(),
            $query->unavailability->isDeletableByUser()
        );
    }
}
