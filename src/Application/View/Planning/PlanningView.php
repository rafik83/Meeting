<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Planning;

class PlanningView
{
    /** @var DayView[] */
    public $days;

    /** @var bool */
    public $isUserMultipleSheet;

    /** @var string */
    public $eventTimeZone;

    /**
     * @param DayView[] $days
     * @param string    $eventTimeZone
     * @param bool      $isUserMultipleSheet
     */
    public function __construct(array $days = [], $eventTimeZone, $isUserMultipleSheet = false)
    {
        $this->days = $days;
        $this->eventTimeZone = $eventTimeZone;
        $this->isUserMultipleSheet = $isUserMultipleSheet;
    }
}
