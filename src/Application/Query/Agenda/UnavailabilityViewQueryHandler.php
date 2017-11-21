<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Application\View\Agenda\UnavailabilityView;
use Proximum\Vimeet\Domain\Time\TimeOverlap;

class UnavailabilityViewQueryHandler
{
    /**
     * @param UnavailabilityViewQuery $query
     *
     * @return UnavailabilityView
     */
    public function handle(UnavailabilityViewQuery $query)
    {
        $begin = TimeOverlap::ceil($query->day->getBegin(), $query->unavailability->getBegin());
        $end = TimeOverlap::floor($query->day->getEnd(), $query->unavailability->getEnd());

        return new UnavailabilityView(
            $query->unavailability->getId(),
            $begin,
            $end,
            $query->event->getTimeZone(),
            $query->unavailability->getMessage()
        );
    }
}
