<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Meeting;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;

class MeetingRequestListViewQuery
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var array
     */
    public $filters;

    /**
     * @var Event
     */
    public $event;

    /**
     * MeetingRequestListViewQuery constructor.
     *
     * @param Event  $event
     * @param Sheet  $sheet
     * @param string $locale
     * @param array  $filters
     */
    public function __construct(Event $event, Sheet $sheet, $locale, array $filters = [])
    {
        $this->event   = $event;
        $this->sheet   = $sheet;
        $this->locale  = $locale;
        $this->filters = $filters;
    }
}
