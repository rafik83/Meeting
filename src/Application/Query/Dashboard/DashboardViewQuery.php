<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Dashboard;

use Proximum\Vimeet\Domain\Model\Event;

class DashboardViewQuery
{
    /**
     * @var Event
     */
    public $event;

    /**
     * DashboardViewQuery constructor.
     *
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
