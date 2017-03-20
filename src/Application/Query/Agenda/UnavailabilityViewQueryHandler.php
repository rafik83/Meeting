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

class UnavailabilityViewQueryHandler
{
    /**
     * @param UnavailabilityViewQuery $query
     *
     * @return UnavailabilityView
     */
    public function handle(UnavailabilityViewQuery $query)
    {
        return new UnavailabilityView(
            $query->unavailability->getId(),
            $query->unavailability->getBegin(),
            $query->unavailability->getEnd(),
            $query->event->getTimeZone(),
            $query->unavailability->getMessage()
        );
    }
}
