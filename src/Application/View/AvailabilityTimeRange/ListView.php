<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\AvailabilityTimeRange;

class ListView
{
    /** @var AvailabilityTimeRangeView[] */
    public $availabilityTimeRangeViews;

    public function __construct(array $availabilityTimeRangeViews = [])
    {
        $this->availabilityTimeRangeViews = $availabilityTimeRangeViews;
    }
}
