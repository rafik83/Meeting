<?php

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
