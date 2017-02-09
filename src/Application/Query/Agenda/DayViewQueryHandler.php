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
     * @var MeetingViewQueryHandler
     */
    private $meetingHandler;

    /**
     * @param HappeningViewQueryHandler          $happeningHandler
     * @param UnavailabilityViewQueryHandler     $unavailabilityHandler
     * @param MassUnavailabilityViewQueryHandler $massHandler
     * @param MeetingViewQueryHandler            $meetingHandler
     */
    public function __construct(
        HappeningViewQueryHandler $happeningHandler,
        UnavailabilityViewQueryHandler $unavailabilityHandler,
        MassUnavailabilityViewQueryHandler $massHandler,
        MeetingViewQueryHandler $meetingHandler
    ) {
        $this->happeningHandler      = $happeningHandler;
        $this->unavailabilityHandler = $unavailabilityHandler;
        $this->massHandler           = $massHandler;
        $this->meetingHandler        = $meetingHandler;
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
        $meetings        = [];

        foreach ($query->happenings as $happening) {
            if ($happening->getHappening()->getBegin() >= $query->day->getStartTime()
                && $happening->getHappening()->getEnd() <= $query->day->getEndTime()
            ) {
                $happeningViews[] = $this->happeningHandler->handle(
                    new HappeningViewQuery(
                        $happening->getHappening(),
                        $query->event,
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
                    new UnavailabilityViewQuery($unavailability, $query->event)
                );
            }
        }

        foreach ($query->masses as $mass) {
            if ($mass->getBegin() >= $query->day->getStartTime()
                && $mass->getEnd() <= $query->day->getEndTime()
            ) {
                $massView = $this->massHandler->handle(
                    new MassUnavailabilityViewQuery(
                        $mass,
                        $query->event,
                        $query->participant,
                        $query->locale
                    )
                );

                if ($massView !== null) {
                    $masses[] = $massView;
                }
            }
        }

        foreach ($query->meetings as $meeting) {
            if ($meeting->getSlot()->getBegin() >= $query->day->getStartTime()
                && $meeting->getSlot()->getEnd() <= $query->day->getEndTime()
            ) {
                $meetings[] = $this->meetingHandler->handle(
                    new MeetingViewQuery(
                        $meeting,
                        $query->currentSheet,
                        $query->event,
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
            $masses,
            $meetings
        );
    }
}
