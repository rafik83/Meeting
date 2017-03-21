<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Planning\Day;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Sheet;

class MeetingViewQuery
{
    /**
     * @var Meeting
     */
    public $meeting;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @param Meeting $meeting
     * @param Sheet   $sheet
     */
    public function __construct(Meeting $meeting, Sheet $sheet)
    {
        $this->meeting = $meeting;
        $this->sheet   = $sheet;
    }
}
