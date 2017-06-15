<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Planning;

use Proximum\Vimeet\Application\View\Planning\DayView;

class PlanningView
{
    /** @var DayView[] */
    public $days;

    /** @var bool */
    public $isUserMultipleSheet;

    /**
     * @param DayView[] $days
     * @param bool      $isUserMultipleSheet
     */
    public function __construct(array $days = [], $isUserMultipleSheet = false)
    {
        $this->days = $days;
        $this->isUserMultipleSheet = $isUserMultipleSheet;
    }
}
