<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Happening;

use Proximum\Vimeet\Application\View\Happening\MassUnavailabilityView;

class MassUnavailabilityViewQueryHandler
{
    /**
     * @param MassUnavailabilityViewQuery $query
     *
     * @return MassUnavailabilityView
     */
    public function handle(MassUnavailabilityViewQuery $query)
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
