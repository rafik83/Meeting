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
use Proximum\Vimeet\Application\View\Agenda\HappeningView;
use Proximum\Vimeet\Application\View\Agenda\MassUnavailabilityView;
use Proximum\Vimeet\Application\View\Agenda\MeetingView;
use Proximum\Vimeet\Application\View\Agenda\UnavailabilityView;

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

    /** @var HappeningView[] */
    private $happeningViews = [];

    /** @var UnavailabilityView[] */
    private $unavailabilities = [];

    /** @var MassUnavailabilityView[] */
    private $masses = [];

    /** @var MeetingView[] */
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
    public function handle(DayViewQuery $query): DayView
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

        // Remove non blocking mass that overlap a blocking mass
        foreach ($this->masses as $massView) {
            $this->handleBlockingMasses($massView);
        }

        // Remove mass that overlap an unavailability
        foreach ($this->masses as $massView) {
            $this->handleMassOverlapOnUnavailability($massView);
        }
    }

    /**
     * @param MassUnavailabilityView $massView
     */
    private function handleBlockingMasses(MassUnavailabilityView $massView)
    {
        if (false !== $nonBlockingMassOverlappedKey = $this->isBlockingMassOverlapNonBlockingMass($massView)) {
            unset($this->masses[$nonBlockingMassOverlappedKey]);
        }
    }

    /**
     * @param MassUnavailabilityView $massView
     */
    private function handleMassOverlapOnUnavailability(MassUnavailabilityView $massView)
    {
        if (false !== $massKey = $this->isMassOverlapUnavailability($massView)) {
            unset($this->masses[$massKey]);
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

    /**
     * @param MassUnavailabilityView $massUnavailabilityView
     * @return bool|int
     */
    private function isMassOverlapUnavailability(MassUnavailabilityView $massUnavailabilityView)
    {
        foreach ($this->unavailabilities as $unavailabilityView) {
            if ($massUnavailabilityView->getBegin() <= $unavailabilityView->getBegin()
                && $massUnavailabilityView->getEnd() >= $unavailabilityView->getEnd()
            ) {
                return array_search($massUnavailabilityView, $this->masses);
            }
        }

        return false;
    }

    /**
     * @param MassUnavailabilityView $massUnavailabilityView
     *
     * @return bool|int
     */
    private function isBlockingMassOverlapNonBlockingMass(MassUnavailabilityView $massUnavailabilityView)
    {
        foreach ($this->masses as $mass) {
            if ($massUnavailabilityView->isBlocking && !$mass->isBlocking) {
                if ($massUnavailabilityView->getBegin() <= $mass->getBegin()
                    && $massUnavailabilityView->getEnd() >= $mass->getEnd()
                ) {
                    return array_search($mass, $this->masses);
                }
            }
        }

        return false;
    }
}
