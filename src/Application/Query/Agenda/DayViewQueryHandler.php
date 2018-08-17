<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Application\Components\Agenda\AgendaCollisionManager;
use Proximum\Vimeet\Application\Components\Agenda\DateTimeZoneConverter;
use Proximum\Vimeet\Application\Query\Agenda\AvailableSheets\AvailableSlotsByParticipantAndDayQuery;
use Proximum\Vimeet\Application\Query\Agenda\AvailableSheets\AvailableSlotsByParticipantAndDayQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\DayView;
use Proximum\Vimeet\Domain\Time\TimeOverlap;

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

    /** @var AvailableSlotsByParticipantAndDayQueryHandler */
    private $availableSlotsByParticipantAndDayQueryHandler;

    /** @var AgendaCollisionManager */
    private $agendaCollisionManager;

    /**
     * @param HappeningViewQueryHandler                      $happeningHandler
     * @param UnavailabilityViewQueryHandler                 $unavailabilityHandler
     * @param MassUnavailabilityViewQueryHandler             $massHandler
     * @param MeetingViewQueryHandler                        $meetingHandler
     * @param CancelAttendanceUnavailabilityViewQueryHandler $cancelAttendanceUnavailabilityViewQueryHandler
     * @param AvailableSlotsByParticipantAndDayQueryHandler  $availableSlotsByParticipantAndDayQueryHandler
     * @param AgendaCollisionManager                         $agendaCollisionManager
     */
    public function __construct(
        HappeningViewQueryHandler $happeningHandler,
        UnavailabilityViewQueryHandler $unavailabilityHandler,
        MassUnavailabilityViewQueryHandler $massHandler,
        MeetingViewQueryHandler $meetingHandler,
        CancelAttendanceUnavailabilityViewQueryHandler $cancelAttendanceUnavailabilityViewQueryHandler,
        AvailableSlotsByParticipantAndDayQueryHandler $availableSlotsByParticipantAndDayQueryHandler,
        AgendaCollisionManager $agendaCollisionManager
    ) {
        $this->happeningHandler                               = $happeningHandler;
        $this->unavailabilityHandler                          = $unavailabilityHandler;
        $this->massHandler                                    = $massHandler;
        $this->meetingHandler                                 = $meetingHandler;
        $this->cancelAttendanceUnavailabilityViewQueryHandler = $cancelAttendanceUnavailabilityViewQueryHandler;
        $this->availableSlotsByParticipantAndDayQueryHandler  = $availableSlotsByParticipantAndDayQueryHandler;
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
                if (TimeOverlap::contains($happening->getHappening(), $query->day)) {
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
                if (TimeOverlap::overlap($unavailability, $query->day)) {
                    $unavailabilities[] = $this->unavailabilityHandler->handle(
                        new UnavailabilityViewQuery($unavailability, $query->event, $query->day)
                    );
                }
            }

            foreach ($query->masses as $mass) {
                if (TimeOverlap::contains($mass, $query->day)) {
                    $massView = $this->massHandler->handle(
                        new MassUnavailabilityViewQuery(
                            $mass,
                            $query->event,
                            $query->participant,
                            $query->locale
                        )
                    );

                    if (null !== $massView) {
                        $masses[] = $massView;
                    }
                }
            }

            foreach ($query->meetings as $meeting) {
                if (TimeOverlap::contains($meeting->getSlot(), $query->day)) {
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

            if ($query->isParticipantUserViewing() && $query->currentSheet->isInCatalog()) {
                $availableSlotViewQuery = new AvailableSlotsByParticipantAndDayQuery(
                    $query->event,
                    $query->participant,
                    $query->day
                );
                $availableSlotViews = $this->availableSlotsByParticipantAndDayQueryHandler->handle($availableSlotViewQuery);
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
