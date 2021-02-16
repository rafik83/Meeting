<?php

namespace Proximum\Vimeet\Application\Query\Planning\Day;

use Proximum\Vimeet\Application\View\Planning\Day\MassView;

class MassViewQueryHandler
{
    /**
     * @param MassViewQuery $query
     *
     * @return MassView
     */
    public function handle(MassViewQuery $query)
    {
        return new MassView(
            $query->mass->getBegin(),
            $query->mass->getEnd(),
            $query->mass->getTitle($query->locale)
        );
    }
}
