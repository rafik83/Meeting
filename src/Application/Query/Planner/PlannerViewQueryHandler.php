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
    private $typePriorityViewQueryHandler;

    /**
     * @param DayViewQueryHandler          $dayViewQueryHandler
     * @param SlotViewQueryHandler         $slotViewQueryHandler
     * @param TypeViewQueryHandler         $typeViewQueryHandler
     * @param TypePriorityViewQueryHandler $typePriorityViewQueryHandler
     */
    public function __construct(
        DayViewQueryHandler $dayViewQueryHandler,
        SlotViewQueryHandler $slotViewQueryHandler,
        TypeViewQueryHandler $typeViewQueryHandler,
        TypePriorityViewQueryHandler $typePriorityViewQueryHandler
    ) {
        $this->dayViewQueryHandler  = $dayViewQueryHandler;
        $this->slotViewQueryHandler = $slotViewQueryHandler;
        $this->typeViewQueryHandler = $typeViewQueryHandler;
        $this->typePriorityViewQueryHandler = $typePriorityViewQueryHandler;
    }

    /**
     * @param PlannerViewQuery $query
     *
     * @return PlannerView
     */
    public function handle(PlannerViewQuery $query)
    {
        $days           = $this->dayViewQueryHandler->handle(new DayViewQuery($query->event->getDays()));
        $slots          = $this->slotViewQueryHandler->handle(new SlotViewQuery($query->event, $days));
        $types          = $this->typeViewQueryHandler->handle(new TypeViewQuery($query->event, $query->locale));
        $typePriorities = $this->typePriorityViewQueryHandler->handle(new TypePriorityViewQuery($query->event, $types));

        return new PlannerView($days, $slots, $types, $typePriorities);
    }
}
