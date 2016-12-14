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
     * @var MassUnavailabilityViewQueryHandler
     */
    private $massHandler;

    /**
     * @param HappeningViewQueryHandler          $happeningHandler
     * @param UnavailabilityViewQueryHandler     $unavailabilityHandler
     * @param MassUnavailabilityViewQueryHandler $massHandler
     */
    public function __construct(
        HappeningViewQueryHandler $happeningHandler,
        UnavailabilityViewQueryHandler $unavailabilityHandler,
        MassUnavailabilityViewQueryHandler $massHandler
    ) {
        $this->happeningHandler      = $happeningHandler;
        $this->unavailabilityHandler = $unavailabilityHandler;
        $this->massHandler           = $massHandler;
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
        $masses          = [];

        foreach ($query->happenings as $happening) {
            if ($happening->getHappening()->getBegin() >= $query->day->getStartTime()
                && $happening->getHappening()->getEnd() <= $query->day->getEndTime()
            ) {
                $happeningViews[] = $this->happeningHandler->handle(
                    new HappeningViewQuery(
                        $happening->getHappening(),
                        $query->locale
                    )
                );
            }
        }

        foreach ($query->unavailabilities as $unavailability) {
            if ($unavailability->getBegin() >= $query->day->getStartTime()
                && $unavailability->getEnd() <= $query->day->getEndTime()
            ) {
                $unavailabilites[] = $this->unavailabilityHandler->handle(
                    new UnavailabilityViewQuery($unavailability)
                );
            }
        }

        foreach ($query->masses as $mass) {
            if ($mass->getBegin() >= $query->day->getStartTime()
                && $mass->getEnd() <= $query->day->getEndTime()
            ) {
                $masses[] = $this->massHandler->handle(
                    new MassUnavailabilityViewQuery(
                        $mass,
                        $query->locale
                    )
                );
            }
        }

        return new DayView(
            $query->day->getStartTime(),
            $query->day->getEndTime(),
            $query->day->getEvent()->getConfiguration()->getScheduleScale(),
            $happeningViews,
            $unavailabilites,
            $masses
        );
    }
}
