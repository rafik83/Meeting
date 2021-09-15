<?php

namespace Proximum\Vimeet\Application\Query\Happening;

use Proximum\Vimeet\Application\View\Happening\HappeningSpeakerView;

class SpeakerViewQueryHandler
{
    /**
     * @param SpeakerViewQuery $query
     *
     * @return HappeningSpeakerView[]
     */
    public function handle(SpeakerViewQuery $query): array
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
