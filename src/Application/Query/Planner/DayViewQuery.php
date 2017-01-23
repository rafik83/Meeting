<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Planner;

use Proximum\Vimeet\Domain\Model\Event\Day;

class DayViewQuery
{
    /** @var Day[] */
    public $days;

    /**
     * @param Day[] $days
     */
    public function __construct(array $days = [])
    {
        $this->days = $days;
    }
}
