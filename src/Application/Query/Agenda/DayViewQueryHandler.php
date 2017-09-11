<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Application\Query\Agenda\AvailableSheets\AvailableSlotsByParticipantQuery;
use Proximum\Vimeet\Application\Query\Agenda\AvailableSheets\AvailableSlotsByParticipantQueryHandler;
use Proximum\Vimeet\Application\Components\Agenda\AgendaCollisionManager;
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

    /** @var AvailableSlotsByParticipantQueryHandler */
    private $availableSlotsByParticipantQueryHandler;

    /** @var AgendaCollisionManager */
    private $agendaCollisionManager;

    /**
     * @param HappeningViewQueryHandler $happeningHandler
     * @param UnavailabilityViewQueryHandler $unavailabilityHandler
     * @param MassUnavailabilityViewQueryHandler $massHandler
     * @param MeetingViewQueryHandler $meetingHandler
     * @param CancelAttendanceUnavailabilityViewQueryHandler $cancelAttendanceUnavailabilityViewQueryHandler
     * @param AvailableSlotsByParticipantQueryHandler $availableSlotsByParticipantQueryHandler
     * @param AgendaCollisionManager $agendaCollisionManager
     */
    public function __construct(
        HappeningViewQueryHandler $happeningHandler,
        UnavailabilityViewQueryHandler $unavailabilityHandler,
        MassUnavailabilityViewQueryHandler $massHandler,
        MeetingViewQueryHandler $meetingHandler,
        CancelAttendanceUnavailabilityViewQueryHandler $cancelAttendanceUnavailabilityViewQueryHandler,
        AvailableSlotsByParticipantQueryHandler $availableSlotsByParticipantQueryHandler,
        AgendaCollisionManager $agendaCollisionManager
    ) {
        $this->happeningHandler                               = $happeningHandler;
        $this->unavailabilityHandler                          = $unavailabilityHandler;
        $this->massHandler                                    = $massHandler;
        $this->meetingHandler                                 = $meetingHandler;
        $this->cancelAttendanceUnavailabilityViewQueryHandler = $cancelAttendanceUnavailabilityViewQueryHandler;
        $this->availableSlotsByParticipantQueryHandler        = $availableSlotsByParticipantQueryHandler;
        $this->agendaCollisionManager                         = $agendaCollisionManager;
    }

    /**
     * @param DayViewQuery $query
     *
     * @return DayView
     */
    public function handle(DayViewQuery $query): DayView
    {
        $cancelAttendanceView = null;
        $happeningViews       = [];
        $unavailabilities     = [];
        $masses               = [];
        $meetings             = [];
        $availableSlotViews   = [];
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

            if ($query->isParticipantUserViewing()) {
                $availableSlotViewQuery = new AvailableSlotsByParticipantQuery(
                    $query->event,
                    $query->participant,
                    $query->day
                );
                $availableSlotViews = $this->availableSlotsByParticipantQueryHandler->handle($availableSlotViewQuery);
            }
        } else {
            $cancelAttendanceView = $this->cancelAttendanceUnavailabilityViewQueryHandler->handle(
                new CancelAttendanceUnavailabilityViewQuery($query->event, $query->day)
            );
        }

        $this->agendaCollisionManager->handleCollision(
            $meetings,
            $happeningViews,
            $unavailabilities,
            $masses
        );

        return new DayView(
            $query->day->getStartTime(),
            $query->day->getEndTime(),
            $query->day->getEvent()->getConfiguration()->getScheduleScale(),
            $this->agendaCollisionManager->getHappeningViews(),
            $this->agendaCollisionManager->getUnavailabilityViews(),
            $this->agendaCollisionManager->getMassViews(),
            $this->agendaCollisionManager->getMeetingViews(),
            $availableSlotViews,
            $cancelAttendanceView
        );
    }
}
