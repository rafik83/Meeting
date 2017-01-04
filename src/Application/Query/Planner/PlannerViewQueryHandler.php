<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Planner;

use Proximum\Vimeet\Application\View\Planner\PlannerView;

class PlannerViewQueryHandler
{
    /**
     * @var DayViewQueryHandler
     */
    private $dayViewQueryHandler;

    /**
     * @var SlotViewQueryHandler
     */
    private $slotViewQueryHandler;

    /**
     * @var TypeViewQueryHandler
     */
    private $typeViewQueryHandler;

    /**
     * @var TypePriorityViewQueryHandler
     */
    private $typePriorityViewQueryHandler;

    /**
     * @var SheetViewQueryHandler
     */
    private $sheetViewQueryHandler;

    /**
     * @var ParticipantViewQueryHandler
     */
    private $participantViewQueryHandler;

    /**
     * @var MeetingViewQueryHandler
     */
    private $meetingViewQueryHandler;

    /**
     * @var SpotViewQueryHandler
     */
    private $spotViewQueryHandler;

    /**
     * @param DayViewQueryHandler          $dayViewQueryHandler
     * @param SlotViewQueryHandler         $slotViewQueryHandler
     * @param TypeViewQueryHandler         $typeViewQueryHandler
     * @param TypePriorityViewQueryHandler $typePriorityViewQueryHandler
     * @param SheetViewQueryHandler        $sheetViewQueryHandler
     * @param ParticipantViewQueryHandler  $participantViewQueryHandler
     * @param MeetingViewQueryHandler      $meetingViewQueryHandler
     * @param SpotViewQueryHandler         $spotViewQueryHandler
     */
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
     * @return PlannerView
     */
    public function handle(PlannerViewQuery $query)
    {
        $event          = $query->event;
        $days           = $this->dayViewQueryHandler->handle(new DayViewQuery($event->getDays()));
        $slots          = $this->slotViewQueryHandler->handle(new SlotViewQuery($event, $days));
        $types          = $this->typeViewQueryHandler->handle(new TypeViewQuery($event, $query->locale));
        $typePriorities = $this->typePriorityViewQueryHandler->handle(new TypePriorityViewQuery($event, $types));
        $sheets         = $this->sheetViewQueryHandler->handle(new SheetViewQuery($event, $types));
        $participants   = $this->participantViewQueryHandler->handle(new ParticipantViewQuery($event, $sheets));
        $meetings       = $this->meetingViewQueryHandler->handle(new MeetingViewQuery($event, $sheets, $participants));
        $spots          = $this->spotViewQueryHandler->handle(new SpotViewQuery($event, $sheets));

        return new PlannerView($days, $slots, $types, $typePriorities, $sheets, $participants, $meetings, $spots);
    }
}
