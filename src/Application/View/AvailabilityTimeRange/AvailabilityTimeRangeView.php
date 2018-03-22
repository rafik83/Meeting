<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\AvailabilityTimeRange;

class AvailabilityTimeRangeView
{
    /** @var string */
    public $name;

    /** @var \DateTimeInterface */
    public $begin;

    /** @var \DateTimeInterface */
    public $end;

    public function __construct(string $name, \DateTimeInterface $begin, \DateTimeInterface $end)
    {
        $this->name = $name;
        $this->begin = $begin;
        $this->end = $end;
    }
}
