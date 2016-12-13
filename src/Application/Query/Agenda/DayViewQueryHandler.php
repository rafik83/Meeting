<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Application\View\Agenda\DayView;

class DayViewQueryHandler
{
    /**
     * @var HappeningViewQueryHandler
     */
    private $happeningHandler;

    /**
     * @var UnavailabilityViewQueryHandler
     */
    private $unavailabilityHandler;

    /**
     * @param HappeningViewQueryHandler $happeningHandler
     * @param UnavailabilityViewQueryHandler  $unavailabilityHandler
     */
    public function __construct(
        HappeningViewQueryHandler $happeningHandler,
        UnavailabilityViewQueryHandler $unavailabilityHandler
    ) {
        $this->happeningHandler      = $happeningHandler;
        $this->unavailabilityHandler = $unavailabilityHandler;
    }

    /**
     * @param DayViewQuery $query
     *
     * @return DayView
     */
    public function handle(DayViewQuery $query)
    {
        $happeningViews  = [];
        $unavailabilites = [];

        $key = 1;
        foreach ($query->happenings as $happening) {
            if ($happening->getHappening()->getBegin() >= $query->day->getStartTime()
                && $happening->getHappening()->getEnd() <= $query->day->getEndTime()
            ) {
                $happeningViews[] = $this->happeningHandler->handle(
                    new HappeningViewQuery(
                        $happening->getHappening(),
                        $query->locale,
                        $key
                    )
                );

                $key++;
            }
        }

        $key = 1;
        foreach ($query->unavailabilities as $unavailability) {
            if ($unavailability->getBegin() >= $query->day->getStartTime()
                && $unavailability->getEnd() <= $query->day->getEndTime()
            ) {
                $happeningViews[] = $this->unavailabilityHandler->handle(
                    new UnavailabilityViewQuery(
                        $unavailability,
                        $query->locale,
                        $key
                    )
                );

                $key++;
            }
        }

        return new DayView(
            $query->day->getStartTime(),
            $query->day->getEndTime(),
            $query->day->getEvent()->getConfiguration()->getScheduleScale(),
            $happeningViews,
            $unavailabilites
        );
    }
}
