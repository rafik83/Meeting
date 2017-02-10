<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
            $query->massAssignment->getMass()->getBegin(),
            $query->massAssignment->getMass()->getEnd(),
            $query->massAssignment->getBegin(),
            $query->massAssignment->getEnd(),
            $query->massAssignment->isEnabled(),
            $query->event->getTimeZone(),
            date_default_timezone_get()
        );
    }
}
