<?php

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
