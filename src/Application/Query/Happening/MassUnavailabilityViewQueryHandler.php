<?php

namespace Proximum\Vimeet\Application\Query\Happening;

use Proximum\Vimeet\Application\View\Happening\MassUnavailabilityView;

class MassUnavailabilityViewQueryHandler
{
    public function handle(MassUnavailabilityViewQuery $query): MassUnavailabilityView
    {
        return new MassUnavailabilityView(
            $query->mass->getId(),
            $query->mass->getBegin(),
            $query->mass->getEnd(),
            $query->mass->getTitle($query->locale),
            $query->mass->getDescription($query->locale),
            $query->mass->getCategory()->getPicto(),
            $query->mass->getCategory()->getLeftColor(),
            $query->mass->getCategory()->getRightColor(),
            $query->event->getTimeZone(),
            $query->mass->isBlocking()
        );
    }
}
