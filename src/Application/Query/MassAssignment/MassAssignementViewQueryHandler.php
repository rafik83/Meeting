<?php

namespace Proximum\Vimeet\Application\Query\MassAssignment;

use Proximum\Vimeet\Application\View\Unavailability\Mass\MassAssignementView;

class MassAssignementViewQueryHandler
{
    /**
     * @param MassAssignmentViewQuery $query
     *
     * @return MassAssignementView
     */
    public function handle(MassAssignmentViewQuery $query)
    {
        return new MassAssignementView(
            $query->massAssignment->getId(),
            $query->massAssignment->getMass()->getTitle($query->locale),
            $query->massAssignment->getMass()->getBegin()->setTimeZone(new \DateTimeZone($query->event->getTimeZone())),
            $query->massAssignment->getMass()->getEnd()->setTimeZone(new \DateTimeZone($query->event->getTimeZone())),
            $query->massAssignment->getBegin()->setTimeZone(new \DateTimeZone($query->event->getTimeZone())),
            $query->massAssignment->getEnd()->setTimeZone(new \DateTimeZone($query->event->getTimeZone())),
            $query->massAssignment->isEnabled(),
            $query->event->getTimeZone(),
            date_default_timezone_get()
        );
    }
}
