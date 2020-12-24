<?php

namespace Proximum\Vimeet\Application\Query\Happening\Admin;

use Proximum\Vimeet\Application\View\Happening\Admin\SpeakerView;

class SpeakerViewQueryHandler
{
    /**
     * @param SpeakerViewQuery $query
     *
     * @return SpeakerView
     */
    public function handle(SpeakerViewQuery $query)
    {
        return new SpeakerView($query->speaker->getFirstname(), $query->speaker->getLastname());
    }
}
