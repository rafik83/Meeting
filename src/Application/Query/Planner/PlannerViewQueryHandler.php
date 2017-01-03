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
     * @param DayViewQueryHandler $dayViewQueryHandler
     */
    public function __construct(DayViewQueryHandler $dayViewQueryHandler)
    {
        $this->dayViewQueryHandler = $dayViewQueryHandler;
    }

    /**
     * @param PlannerViewQuery $query
     *
     * @return PlannerView
     */
    public function handle(PlannerViewQuery $query)
    {
        $days = $this->dayViewQueryHandler->handle(new DayViewQuery($query->event->getDays()));

        return new PlannerView($days);
    }
}
