<?php

namespace Proximum\Vimeet\Application\Query\Planner;

use Proximum\Vimeet\Application\Exception\Planner\DayNotConfiguredException;
use Proximum\Vimeet\Application\View\Planner\Day;

class DayViewQueryHandler
{
    /**
     * @param DayViewQuery $query
     *
     * @throws DayNotConfiguredException
     *
     * @return Day[]
     */
    public function handle(DayViewQuery $query)
    {
        $days = [];

        if (empty($query->days)) {
            throw new DayNotConfiguredException();
        }

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
