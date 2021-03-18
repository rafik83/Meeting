<?php

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Application\Components\Agenda\AgendaCollisionManager;
use Proximum\Vimeet\Application\Query\Agenda\AvailableSheets\AvailableSlotsByParticipantAndDayQuery;
use Proximum\Vimeet\Application\Query\Agenda\AvailableSheets\AvailableSlotsByParticipantAndDayQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\DayView;
use Proximum\Vimeet\Application\View\Agenda\UnavailabilityView;
use Proximum\Vimeet\Domain\Time\OverlappedTimeRangeMerger;
use Proximum\Vimeet\Domain\Time\TimeOverlap;
use Proximum\Vimeet\Domain\Time\TimeRangeView;

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

    /** @var OverlappedTimeRangeMerger */
    private $overlappedTimeRangeMerger;

    public function __construct(
        HappeningViewQueryHandler $happeningHandler,
        UnavailabilityViewQueryHandler $unavailabilityHandler,
        MassUnavailabilityViewQueryHandler $massHandler,
        MeetingViewQueryHandler $meetingHandler,
        CancelAttendanceUnavailabilityViewQueryHandler $cancelAttendanceUnavailabilityViewQueryHandler,
        AvailableSlotsByParticipantAndDayQueryHandler $availableSlotsByParticipantAndDayQueryHandler,
        AgendaCollisionManager $agendaCollisionManager,
        OverlappedTimeRangeMerger $overlappedTimeRangeMerger
    ) {
        $this->happeningHandler = $happeningHandler;
        $this->unavailabilityHandler = $unavailabilityHandler;
        $this->massHandler = $massHandler;
        $this->meetingHandler = $meetingHandler;
        $this->cancelAttendanceUnavailabilityViewQueryHandler = $cancelAttendanceUnavailabilityViewQueryHandler;
        $this->availableSlotsByParticipantAndDayQueryHandler = $availableSlotsByParticipantAndDayQueryHandler;
        $this->agendaCollisionManager = $agendaCollisionManager;
        $this->overlappedTimeRangeMerger = $overlappedTimeRangeMerger;
    }

    public function handle(DayViewQuery $query): DayView
    {
        $cancelAttendanceView = null;
        $happeningViews = [];
        $unavailabilityViews = [];
        $masses = [];
        $meetings = [];
        $availableSlotViews = [];
        $agendaSlots = [];

        if ($query->currentSheet->attend()) {
            foreach ($query->happenings as $happening) {
                if (TimeOverlap::beginIn($happening->getHappening(), $query->day)) {
                    $happeningViews[] = $this->happeningHandler->handle(
                        new HappeningViewQuery(
                            $query->participant->getUser(),
                            $happening->getHappening(),
                            $query->event,
                            $query->locale
                        )
                    );
                }
            }

            foreach ($query->unavailabilities as $unavailability) {
                if (TimeOverlap::overlap($unavailability, $query->day)) {
                    $unavailabilityViews[] = $this->unavailabilityHandler->handle(
                        new UnavailabilityViewQuery($unavailability, $query->event, $query->day)
                    );
                }
            }

            foreach ($query->masses as $mass) {
                if (TimeOverlap::beginIn($mass, $query->day)) {
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
                if (TimeOverlap::beginIn($meeting->getSlot(), $query->day)) {
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

            foreach ($query->meetingSlots as $meetingSlot) {
                if (TimeOverlap::beginIn($meetingSlot, $query->day)) {
                    $agendaSlots[] = new TimeRangeView($meetingSlot->getBegin(), $meetingSlot->getEnd());
                }
            }

            if ($query->isParticipantUserViewing() && $query->currentSheet->isInInternalCatalog()) {
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
            $unavailabilityViews,
            $masses
        );

        return new DayView(
            $query->day->getBegin(),
            $query->day->getEnd(),
            $query->event->getConfiguration()->getScheduleScale(),
            $this->agendaCollisionManager->getHappeningViews(),
            $this->agendaCollisionManager->getUnavailabilityViews(),
            $this->agendaCollisionManager->getMassViews(),
            $this->agendaCollisionManager->getMeetingViews(),
            $agendaSlots,
            $availableSlotViews,
            $cancelAttendanceView,
            $this->isUnavailableForThisDay($query->day, $unavailabilityViews)
        );
    }

    /**
     * @param TimeRangeView        $day
     * @param UnavailabilityView[] $unavailabilityViews
     *
     * @return bool
     */
    private function isUnavailableForThisDay(TimeRangeView $day, array $unavailabilityViews): bool
    {
        $unavailabilityTimeRangeViews = [];

        foreach ($unavailabilityViews as $unavailabilityView) {
            $unavailabilityTimeRangeViews[] = new TimeRangeView(
                $unavailabilityView->getBegin(),
                $unavailabilityView->getEnd()
            );
        }

        $mergedUnavailabilityTimeRangeViews = $this->overlappedTimeRangeMerger->merge($unavailabilityTimeRangeViews);

        foreach ($mergedUnavailabilityTimeRangeViews as $unavailabilityTimeRangeView) {
            if (TimeOverlap::contains($day, $unavailabilityTimeRangeView)) {
                return true;
            }
        }

        return false;
    }
}
