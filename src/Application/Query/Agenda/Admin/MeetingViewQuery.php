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
use Proximum\Vimeet\Domain\Model\Sheet;

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
     * @var Sheet
     */
    public $sheet;

    /**
     * @param Meeting $meeting
     * @param Sheet   $sheet
     * @param string  $locale
     */
    public function __construct(Meeting $meeting, Sheet $sheet, $locale)
    {
        $this->meeting = $meeting;
        $this->locale  = $locale;
        $this->sheet   = $sheet;
    }
}
