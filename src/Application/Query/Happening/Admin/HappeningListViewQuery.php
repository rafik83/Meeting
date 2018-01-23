<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Happening\Admin;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;

class HappeningListViewQuery implements Query
{
    /**
     * @var string
     */
    public $locale;

    /**
     * @var Event
     */
    public $event;

    /**
     * @param Event  $event
     * @param string $locale
     */
    public function __construct(Event $event, $locale)
    {
        $this->event = $event;
        $this->locale = $locale;
    }
}
