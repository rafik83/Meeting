<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Happening;

use Proximum\Vimeet\Domain\Model\Event;

class HappeningViewQuery
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var int
     */
    public $day;

    /**
     * HappeningViewQuery constructor.
     *
     * @param Event  $event
     * @param string $locale
     * @param int    $day
     */
    public function __construct(Event $event, $locale, $day)
    {
        $this->event  = $event;
        $this->locale = $locale;
        $this->day    = $day;
    }
}
