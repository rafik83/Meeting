<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\Spot;

use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Spot\SpotSatisfactionView;

class SpotSatisfactionViewQueryHandler
{
    /**
     * @param SpotSatisfactionViewQuery $query
     *
     * @return SpotSatisfactionView
     */
    public function handle(SpotSatisfactionViewQuery $query): SpotSatisfactionView
    {
        $availability = $query->numberOfSlotAvailable - count($query->spot->getSpotUnavailabilities());

        if (0 === $availability) {
            $availability = 1;
        }

        $satisfaction = ($query->numberOfMeeting / ($availability * $query->spot->getMeetingCapacity())) * 100;

        return new SpotSatisfactionView(
            $query->spot->getId(),
            $query->spot->getReference(),
            !$query->spot->hasSheets(),
            $query->spot->isVisio(),
            $query->spot->getPriority(),
            $satisfaction
        );
    }
}
