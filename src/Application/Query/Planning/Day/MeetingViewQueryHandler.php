<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Planning\Day;

use Proximum\Vimeet\Application\View\Planning\Day\MeetingView;

class MeetingViewQueryHandler
{
    /**
     * @param MeetingViewQuery $query
     *
     * @return MeetingView
     */
    public function handle(MeetingViewQuery $query)
    {
        $userSheet = $query->meeting->getSheetOfUser($query->user);

        return new MeetingView(
            $query->meeting->getSlot()->getBegin(),
            $query->meeting->getSlot()->getEnd(),
            $query->meeting->getSpot()->getReference(),
            $userSheet->getTitle(),
            $query->meeting->getSheetMet($userSheet)->getTitle()
        );
    }
}
