<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
