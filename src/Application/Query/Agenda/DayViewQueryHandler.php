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
    /** @var HappeningViewQueryHandler */
    private $happeningHandler;

    /** @var UnavailabilityViewQueryHandler */
    private $unavailabilityHandler;

    /** @var MassUnavailabilityViewQueryHandler */
    private $massHandler;

    /** @var MeetingViewQueryHandler */
    private $meetingHandler;

    /** @var CancelAttendanceUnavailabilityViewQueryHandler */
    private $cancelAttendanceUnavailabilityViewQueryHandler;

    private $happeningViews = [];

    private $unavailabilities = [];

    private $masses = [];

    private $meetings = [];

    /**
     * @param HappeningViewQueryHandler                      $happeningHandler
     * @param UnavailabilityViewQueryHandler                 $unavailabilityHandler
     * @param MassUnavailabilityViewQueryHandler             $massHandler
     * @param MeetingViewQueryHandler                        $meetingHandler
     * @param CancelAttendanceUnavailabilityViewQueryHandler $cancelAttendanceUnavailabilityViewQueryHandler
     */
    public function __construct(
        HappeningViewQueryHandler $happeningHandler,
        UnavailabilityViewQueryHandler $unavailabilityHandler,
        MassUnavailabilityViewQueryHandler $massHandler,
        MeetingViewQueryHandler $meetingHandler,
        CancelAttendanceUnavailabilityViewQueryHandler $cancelAttendanceUnavailabilityViewQueryHandler
    ) {
        $this->happeningHandler                               = $happeningHandler;
        $this->unavailabilityHandler                          = $unavailabilityHandler;
        $this->massHandler                                    = $massHandler;
        $this->meetingHandler                                 = $meetingHandler;
        $this->cancelAttendanceUnavailabilityViewQueryHandler = $cancelAttendanceUnavailabilityViewQueryHandler;
    }

    /**
     * @param DayViewQuery $query
     *
     * @return DayView
     */
    public function handle(DayViewQuery $query)
    {
        if ($query->currentSheet->attend()) {
            $this->handleHappenings($query);
            $this->handleUnavailabilities($query);
            $this->handleMasses($query);
            $this->handleMeetings($query);
        } else {
            $cancelAttendanceView = $this->cancelAttendanceUnavailabilityViewQueryHandler->handle(
                new CancelAttendanceUnavailabilityViewQuery($query->event, $query->day)
            );
        }

        return new DayView(
            $query->day->getStartTime(),
            $query->day->getEndTime(),
            $query->day->getEvent()->getConfiguration()->getScheduleScale(),
            $this->happeningViews,
            $this->unavailabilities,
            $this->masses,
            $this->meetings,
            $cancelAttendanceView ?? null
        );
    }

    /**
     * @param DayViewQuery $query
     */
    private function handleHappenings(DayViewQuery $query)
    {
        foreach ($query->happenings as $happening) {
            if ($query->day->contain($happening->getHappening())) {
                $this->happeningViews[] = $this->happeningHandler->handle(
                    new HappeningViewQuery(
                        $happening->getHappening(),
                        $query->event,
                        $query->locale
                    )
                );
            }
        }
    }

    /**
     * @param DayViewQuery $query
     */
    private function handleUnavailabilities(DayViewQuery $query)
    {
        foreach ($query->unavailabilities as $unavailability) {
            if ($query->day->contain($unavailability)) {
                $this->unavailabilities[] = $this->unavailabilityHandler->handle(
                    new UnavailabilityViewQuery($unavailability, $query->event)
                );
            }
        }

    }

    /**
     * @param DayViewQuery $query
     */
    private function handleMasses(DayViewQuery $query)
    {
        foreach ($query->masses as $mass) {
            if ($query->day->contain($mass)) {
                $massView = $this->massHandler->handle(
                    new MassUnavailabilityViewQuery(
                        $mass,
                        $query->event,
                        $query->participant,
                        $query->locale
                    )
                );

                if ($massView !== null) {
                    $this->masses[] = $massView;
                }
            }
        }
    }

    /**
     * @param DayViewQuery $query
     */
    private function handleMeetings(DayViewQuery $query)
    {
        foreach ($query->meetings as $meeting) {
            if ($query->day->contain($meeting->getSlot())) {
                $this->meetings[] = $this->meetingHandler->handle(
                    new MeetingViewQuery(
                        $meeting,
                        $query->currentSheet,
                        $query->isUserParticipantMultipleSheet,
                        $query->participant->getUser(),
                        $query->event,
                        $query->locale
                    )
                );
            }
        }
    }
}
