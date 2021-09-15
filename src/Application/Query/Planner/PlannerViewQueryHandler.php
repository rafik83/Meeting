<?php

namespace Proximum\Vimeet\Application\Query\Planner;

use Proximum\Vimeet\Application\Exception\Planner;
use Proximum\Vimeet\Application\View\Planner\PlannerView;

class PlannerViewQueryHandler
{
    /** @var DayViewQueryHandler */
    private $dayViewQueryHandler;

    /** @var SlotViewQueryHandler */
    private $slotViewQueryHandler;

    /** @var TypeViewQueryHandler */
    private $typeViewQueryHandler;

    /** @var TypePriorityViewQueryHandler */
    private $typePriorityViewQueryHandler;

    /** @var SheetViewQueryHandler */
    private $sheetViewQueryHandler;

    /** @var ParticipantViewQueryHandler */
    private $participantViewQueryHandler;

    /** @var MeetingViewQueryHandler */
    private $meetingViewQueryHandler;

    /** @var SpotViewQueryHandler */
    private $spotViewQueryHandler;

    public function __construct(
        DayViewQueryHandler $dayViewQueryHandler,
        SlotViewQueryHandler $slotViewQueryHandler,
        TypeViewQueryHandler $typeViewQueryHandler,
        TypePriorityViewQueryHandler $typePriorityViewQueryHandler,
        SheetViewQueryHandler $sheetViewQueryHandler,
        ParticipantViewQueryHandler $participantViewQueryHandler,
        MeetingViewQueryHandler $meetingViewQueryHandler,
        SpotViewQueryHandler $spotViewQueryHandler
    ) {
        $this->dayViewQueryHandler          = $dayViewQueryHandler;
        $this->slotViewQueryHandler         = $slotViewQueryHandler;
        $this->typeViewQueryHandler         = $typeViewQueryHandler;
        $this->typePriorityViewQueryHandler = $typePriorityViewQueryHandler;
        $this->sheetViewQueryHandler        = $sheetViewQueryHandler;
        $this->participantViewQueryHandler  = $participantViewQueryHandler;
        $this->meetingViewQueryHandler      = $meetingViewQueryHandler;
        $this->spotViewQueryHandler         = $spotViewQueryHandler;
    }

    /**
     * @param PlannerViewQuery $query
     *
     * @throws Planner\SlotNotConfiguredException
     * @throws Planner\DayNotConfiguredException
     *
     * @return PlannerView
     */
    public function handle(PlannerViewQuery $query): PlannerView
    {
        $event = $query->event;
        $days = $this->dayViewQueryHandler->handle(new DayViewQuery($event->getDays()));
        $slots = $this->slotViewQueryHandler->handle(new SlotViewQuery($event, $days));
        $types  = $this->typeViewQueryHandler->handle(new TypeViewQuery($event, $query->locale));
        $typePriorities = $this->typePriorityViewQueryHandler->handle(new TypePriorityViewQuery($event, $types));
        $sheets = $this->sheetViewQueryHandler->handle(
            new SheetViewQuery($event, $types, $query->exportSolutionType)
        );
        $participants = $this->participantViewQueryHandler->handle(new ParticipantViewQuery($event, $sheets, $slots));
        $spots = $this->spotViewQueryHandler->handle(new SpotViewQuery($event, $sheets, $slots));
        $meetings = $this->meetingViewQueryHandler->handle(
            new MeetingViewQuery($event, $sheets, $participants, $slots, $spots, $query->exportSolutionType)
        );

        return new PlannerView($days, $slots, $types, $typePriorities, $sheets, $participants, $meetings, $spots);
    }
}
