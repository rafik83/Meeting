<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Domain\Model\Meeting;

class MeetingViewQuery
{
    /**
     * @var Meeting
     */
    public $meeting;

    /**
     * @var string
     */
    public $locale;

    /**
     * @param Meeting   $meeting
     * @param string $locale
     */
    public function __construct(Meeting $meeting, $locale)
    {
        $this->meeting   = $meeting;
        $this->locale = $locale;
    }
}
