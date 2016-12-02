<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Dashboard;

use Proximum\Vimeet\Domain\Model\Event;

class DashboardSheetViewQuery
{
    /**
     * @var Event
     */
    public $event;

    /**
     * DashboardSheetViewQuery constructor.
     *
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
