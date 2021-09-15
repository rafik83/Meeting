<?php

namespace Proximum\Vimeet\Application\Query\Planning\Day;

use Proximum\Vimeet\Application\View\Planning\Day\AssignmentView;

class AssignmentViewQueryHandler
{
    /**
     * @param AssignmentViewQuery $query
     *
     * @return AssignmentView
     */
    public function handle(AssignmentViewQuery $query)
    {
        return new AssignmentView(
            $query->assignment->getBegin(),
            $query->assignment->getEnd(),
            $query->assignment->getMass()->getTitle($query->locale)
        );
    }
}
