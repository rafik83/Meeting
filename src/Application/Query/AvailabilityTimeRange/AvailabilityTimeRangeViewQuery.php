<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\AvailabilityTimeRange;

use Proximum\Vimeet\Domain\Model\AvailabilityTimeRange;

class AvailabilityTimeRangeViewQuery
{
    /** @var AvailabilityTimeRange */
    public $availabilityTimeRange;

    public function __construct(AvailabilityTimeRange $availabilityTimeRange)
    {
        $this->availabilityTimeRange = $availabilityTimeRange;
    }
}
