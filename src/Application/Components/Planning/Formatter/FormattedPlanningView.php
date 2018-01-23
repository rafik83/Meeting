<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Planning\Formatter;

class FormattedPlanningView
{
    /** @var array of string */
    public $days;

    /** @var string */
    public $unallocated;

    /**
     * @param array  $days
     * @param string $unallocated
     */
    public function __construct(array $days, string $unallocated)
    {
        $this->days = $days;
        $this->unallocated = $unallocated;
    }
}
