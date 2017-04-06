<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Domain\Model\Event;
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
    public $currentSheet;

    /**
     * @var Event
     */
    public $event;

    /**
     * @param Meeting $meeting
     * @param Sheet   $currentSheet
     * @param Event   $event
     * @param string  $locale
     */
    public function __construct(Meeting $meeting, Sheet $currentSheet, Event $event, $locale)
    {
        $this->meeting      = $meeting;
        $this->locale       = $locale;
        $this->currentSheet = $currentSheet;
        $this->event        = $event;
    }
}
