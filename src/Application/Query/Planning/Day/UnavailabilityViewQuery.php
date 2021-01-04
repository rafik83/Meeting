<?php

namespace Proximum\Vimeet\Application\Query\Planning\Day;

use Proximum\Vimeet\Domain\Model\Unavailability;

class UnavailabilityViewQuery
{
    /** @var Unavailability */
    public $unavailability;

    /**
     * @param Unavailability $unavailability
     */
    public function __construct(Unavailability $unavailability)
    {
        $this->unavailability = $unavailability;
    }
}
