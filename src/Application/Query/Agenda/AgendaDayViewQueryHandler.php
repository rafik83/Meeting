<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Application\View\Agenda\AgendaDayView;
use Proximum\Vimeet\Application\View\Agenda\DayView;

class AgendaDayViewQueryHandler
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
     * @var MeetingViewQueryHandler
     */
    private $meetingViewQueryHandler;

    /**
     * @param HappeningViewQueryHandler          $happeningHandler
     * @param UnavailabilityViewQueryHandler     $unavailabilityHandler
     * @param MassUnavailabilityViewQueryHandler $massHandler
     * @param MeetingViewQueryHandler            $meetingViewQueryHandler
     */
    public function __construct(
        HappeningViewQueryHandler $happeningHandler,
        UnavailabilityViewQueryHandler $unavailabilityHandler,
        MassUnavailabilityViewQueryHandler $massHandler,
        MeetingViewQueryHandler $meetingViewQueryHandler
    ) {
        $this->happeningHandler      = $happeningHandler;
        $this->unavailabilityHandler = $unavailabilityHandler;
        $this->massHandler           = $massHandler;
        $this->meetingViewQueryHandler = $meetingViewQueryHandler;
    }

    /**
     * @param DayViewQuery $query
     *
     * @return DayView
     */
    public function handle(AgendaDayViewQuery $query)
    {
        $happeningViews  = [];
        $unavailabilites = [];
        $masses          = [];
        $meetings        = [];

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

        foreach ($query->meetings as $meeting) {
            if ($meeting->getBegin() >= $query->day->getStartTime()
                && $meeting->getEnd() <= $query->day->getEndTime()
            ) {
                $meetings[] = $this->meetingViewQueryHandler->handle(
                    new MeetingViewQuery(
                        $meeting,
                        $query->locale
                    )
                );
            }
        }

        return new AgendaDayView(
            $query->day->getStartTime(),
            $query->day->getEndTime(),
            $query->day->getEvent()->getConfiguration()->getScheduleScale(),
            $happeningViews,
            $unavailabilites,
            $masses,
            $meetings
        );
    }
}
