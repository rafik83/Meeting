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
        $happeningViews       = [];
        $unavailabilities     = [];
        $masses               = [];
        $meetings             = [];
        $cancelAttendanceView = null;

        if ($query->currentSheet->attend()) {
            foreach ($query->happenings as $happening) {
                if ($query->day->contain($happening->getHappening())) {
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
                if ($query->day->contain($unavailability)) {
                    $unavailabilities[] = $this->unavailabilityHandler->handle(
                        new UnavailabilityViewQuery($unavailability, $query->event)
                    );
                }
            }

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
                        $masses[] = $massView;
                    }
                }
            }

            foreach ($query->meetings as $meeting) {
                if ($query->day->contain($meeting->getSlot())) {
                    $meetings[] = $this->meetingHandler->handle(
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
        } else {
            $cancelAttendanceView = $this->cancelAttendanceUnavailabilityViewQueryHandler->handle(
                new CancelAttendanceUnavailabilityViewQuery($query->event, $query->day)
            );
        }

        return new DayView(
            $query->day->getStartTime(),
            $query->day->getEndTime(),
            $query->day->getEvent()->getConfiguration()->getScheduleScale(),
            $happeningViews,
            $unavailabilities,
            $masses,
            $meetings,
            $cancelAttendanceView
        );
    }
}
