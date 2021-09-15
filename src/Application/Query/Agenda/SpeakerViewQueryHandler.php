<?php

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Application\View\Agenda\SpeakerView;

class SpeakerViewQueryHandler
{
    /**
     * @param SpeakerViewQuery $query
     *
     * @return SpeakerView[]
     */
    public function handle(SpeakerViewQuery $query)
    {
        $happeningSpeakerView = [];

        foreach ($query->happening->getSpeakers() as $speaker) {
            $happeningSpeakerView[] = new SpeakerView(
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
