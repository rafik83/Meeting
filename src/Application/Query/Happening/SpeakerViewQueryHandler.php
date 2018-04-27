<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Happening;

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
