<?php

namespace Proximum\Vimeet\Application\Query\Planning\Day;

use Proximum\Vimeet\Application\View\Planning\Day\UnavailabilityView;

class UnavailabilityViewQueryHandler
{
    /**
     * @param UnavailabilityViewQuery $query
     *
     * @return UnavailabilityView
     */
    public function handle(UnavailabilityViewQuery $query)
    {
        return new UnavailabilityView(
            $query->unavailability->getBegin(),
            $query->unavailability->getEnd(),
            $query->unavailability->getMessage()
        );
    }
}
