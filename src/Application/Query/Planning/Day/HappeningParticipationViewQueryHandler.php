<?php

namespace Proximum\Vimeet\Application\Query\Planning\Day;

use Proximum\Vimeet\Application\View\Planning\Day\HappeningParticipationView;

class HappeningParticipationViewQueryHandler
{
    /**
     * @param HappeningParticipationViewQuery $query
     *
     * @return HappeningParticipationView
     */
    public function handle(HappeningParticipationViewQuery $query)
    {
        return new HappeningParticipationView(
            $query->happeningParticipation->getHappening()->getBegin(),
            $query->happeningParticipation->getHappening()->getEnd(),
            $query->happeningParticipation->getHappening()->getTitle($query->locale)
        );
    }
}
