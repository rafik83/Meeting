<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Planning;

use Proximum\Vimeet\Application\Query\Planning\Day\AssignmentViewQuery;
use Proximum\Vimeet\Application\Query\Planning\Day\AssignmentViewQueryHandler;
use Proximum\Vimeet\Application\Query\Planning\Day\HappeningParticipationViewQuery;
use Proximum\Vimeet\Application\Query\Planning\Day\HappeningParticipationViewQueryHandler;
use Proximum\Vimeet\Application\Query\Planning\Day\MassViewQuery;
use Proximum\Vimeet\Application\Query\Planning\Day\MassViewQueryHandler;
use Proximum\Vimeet\Application\Query\Planning\Day\MeetingViewQuery;
use Proximum\Vimeet\Application\Query\Planning\Day\MeetingViewQueryHandler;
use Proximum\Vimeet\Application\Query\Planning\Day\UnavailabilityViewQuery;
use Proximum\Vimeet\Application\Query\Planning\Day\UnavailabilityViewQueryHandler;
use Proximum\Vimeet\Application\View\Planning\DayView;

class DayViewQueryHandler
{

    /** @var HappeningParticipationViewQueryHandler */
    private $happeningHandler;

    /** @var MassViewQueryHandler */
    private $massHandler;

    /** @var AssignmentViewQueryHandler */
    private $assignmentHandler;

    /** @var UnavailabilityViewQueryHandler */
    private $unavailabilityHandler;

    /** @var MeetingViewQueryHandler */
    private $meetingHandler;

    /**
     * @param HappeningParticipationViewQueryHandler $happeningHandler
     * @param MassViewQueryHandler                   $massHandler
     * @param AssignmentViewQueryHandler             $assignmentHandler
     * @param UnavailabilityViewQueryHandler         $unavailabilityHandler
     * @param MeetingViewQueryHandler                $meetingHandler
     */
    public function __construct(
        HappeningParticipationViewQueryHandler $happeningHandler,
        MassViewQueryHandler $massHandler,
        AssignmentViewQueryHandler $assignmentHandler,
        UnavailabilityViewQueryHandler $unavailabilityHandler,
        MeetingViewQueryHandler $meetingHandler
    ) {
        $this->happeningHandler      = $happeningHandler;
        $this->massHandler           = $massHandler;
        $this->assignmentHandler     = $assignmentHandler;
        $this->unavailabilityHandler = $unavailabilityHandler;
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
        $assignments     = [];
        $unavailabilites = [];
        $masses          = [];
        $meetings        = [];

        foreach ($query->happenings as $happening) {
            if ($happening->getHappening()->getBegin() >= $query->day->getStartTime()
                && $happening->getHappening()->getEnd() <= $query->day->getEndTime()
            ) {
                $happeningViews[] = $this->happeningHandler->handle(
                    new HappeningParticipationViewQuery(
                        $happening,
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
                    new MassViewQuery(
                        $mass,
                        $query->locale
                    )
                );
            }
        }

        foreach ($query->meetings as $meeting) {
            if ($meeting->getSlot()->getBegin() >= $query->day->getStartTime()
                && $meeting->getSlot()->getEnd() <= $query->day->getEndTime()
            ) {
                $meetings[] = $this->meetingHandler->handle(
                    new MeetingViewQuery(
                        $meeting,
                        $query->sheet
                    )
                );
            }
        }

        foreach ($query->assignments as $assignment) {
            if ($assignment->getBegin() >= $query->day->getStartTime()
                && $assignment->getEnd() <= $query->day->getEndTime()
            ) {
                $assignments[] = $this->assignmentHandler->handle(
                    new AssignmentViewQuery(
                        $assignment,
                        $query->locale
                    )
                );
            }
        }

        return new DayView(
            $query->day->getStartTime(),
            $query->day->getEndTime(),
            $happeningViews,
            $unavailabilites,
            $masses,
            $assignments,
            $meetings
        );
    }
}
