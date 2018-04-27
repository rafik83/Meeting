<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Meeting;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;

class MeetingSheetViewQuery
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var string
     */
    public $locale;

    /**
     * MeetingSheetViewQuery constructor.
     *
     * @param Event  $event
     * @param Sheet  $sheet
     * @param string $locale
     */
    public function __construct(Event $event, Sheet $sheet, $locale)
    {
        $this->event  = $event;
        $this->sheet  = $sheet;
        $this->locale = $locale;
    }
}
