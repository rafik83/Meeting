<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Planner;

use Proximum\Vimeet\Application\View\Planner\Day;

class DayViewQueryHandler
{
    /**
     * @param DayViewQuery $query
     *
     * @return Day[]
     */
    public function handle(DayViewQuery $query)
    {
        $days = [];

        foreach ($query->days as $day) {
            $days[] = new Day(
                $day->getId(),
                intval($day->getDay()->format('d')),
                intval($day->getDay()->format('m')),
                intval($day->getDay()->format('Y'))
            );
        }

        return $days;
    }
}
