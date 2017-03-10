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
    /**
     * @var array
     */
    public $days;

    /**
     * @param array $days
     */
    public function __construct(array $days = [])
    {
        $this->days = $days;
    }
}
