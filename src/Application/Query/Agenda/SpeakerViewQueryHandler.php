<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Application\View\Happening\HappeningSpeakerView;

class SpeakerViewQueryHandler
{
    /**
     * @param SpeakerViewQuery $query
     *
     * @return HappeningSpeakerView[]
     */
    public function handle(SpeakerViewQuery $query)
    {
        $happeningSpeakerView = [];

        foreach ($query->happening->getSpeakers() as $speaker) {
            $happeningSpeakerView[] = new HappeningSpeakerView(
                $speaker->getFirstname(),
                $speaker->getLastname(),
                $speaker->getPosition($query->locale),
                $speaker->getPhoto(),
                $speaker->getLogo()
            );
        }

        return $happeningSpeakerView;
    }
}
