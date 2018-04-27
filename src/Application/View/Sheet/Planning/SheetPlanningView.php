<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet\Planning;

class SheetPlanningView
{
    /**
     * @var string
     */
    public $planning;

    /**
     * @param string $planning the sheet planning in html format
     */
    public function __construct($planning)
    {
        $this->planning = $planning;
    }
}
