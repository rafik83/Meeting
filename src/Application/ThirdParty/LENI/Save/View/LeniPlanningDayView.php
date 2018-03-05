<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Save\View;

class LeniPlanningDayView
{
    /** @var string */
    public $planning;

    /**
     * @param string $planning
     */
    public function __construct(string $planning)
    {
        $this->planning = $planning;
    }
}
