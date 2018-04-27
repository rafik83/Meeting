<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda\Admin\Spot;

use Proximum\Vimeet\Application\View\Agenda\AgendaDayView;

class DaySpotViewQueryHandler
{
    /**
     * @var SlotViewQueryHandler
     */
    private $slotViewQueryHandler;

    /**
     * DaySpotViewQueryHandler constructor.
     *
     * @param SlotViewQueryHandler $slotViewQueryHandler
     */
    public function __construct(SlotViewQueryHandler $slotViewQueryHandler)
    {
        $this->slotViewQueryHandler = $slotViewQueryHandler;
    }

    /**
     * @param DaySpotViewQuery $query
     *
     * @return AgendaDayView
     */
    public function handle(DaySpotViewQuery $query)
    {
        $slotViews = $this->slotViewQueryHandler->handle(
            new SlotViewQuery(
                $query->event,
                $query->day,
                $query->spot,
                $query->locale
            )
        );

        return new AgendaDayView($query->dayNumber, $slotViews);
    }
}
