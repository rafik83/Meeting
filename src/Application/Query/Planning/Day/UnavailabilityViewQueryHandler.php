<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Planning\Day;

use Proximum\Vimeet\Application\View\Planning\Day\UnavailabilityView;

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
            $query->unavailability->getBegin(),
            $query->unavailability->getEnd(),
            $query->unavailability->getMessage()
        );
    }
}
