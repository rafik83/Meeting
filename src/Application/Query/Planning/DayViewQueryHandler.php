<?php

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
use Proximum\Vimeet\Domain\Time\TimeOverlap;

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
        $happeningViews   = [];
        $assignments      = [];
        $unavailabilities = [];
        $masses           = [];
        $meetings         = [];

        foreach ($query->happenings as $happening) {
            if (TimeOverlap::contains($happening->getHappening(), $query->day)) {
                $happeningViews[] = $this->happeningHandler->handle(
                    new HappeningParticipationViewQuery(
                        $happening,
                        $query->locale
                    )
                );
            }
        }

        foreach ($query->unavailabilities as $unavailability) {
            if (TimeOverlap::contains($unavailability, $query->day)) {
                $unavailabilities[] = $this->unavailabilityHandler->handle(
                    new UnavailabilityViewQuery($unavailability)
                );
            }
        }

        foreach ($query->masses as $mass) {
            if (TimeOverlap::contains($mass, $query->day)) {
                $masses[] = $this->massHandler->handle(
                    new MassViewQuery(
                        $mass,
                        $query->locale
                    )
                );
            }
        }

        foreach ($query->meetings as $meeting) {
            if (TimeOverlap::contains($meeting->getSlot(), $query->day)) {
                $meetings[] = $this->meetingHandler->handle(
                    new MeetingViewQuery(
                        $query->event,
                        $meeting,
                        $query->user,
                        $query->locale
                    )
                );
            }
        }

        foreach ($query->assignments as $assignment) {
            if (TimeOverlap::contains($assignment, $query->day)) {
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
            $unavailabilities,
            $masses,
            $assignments,
            $meetings
        );
    }
}
