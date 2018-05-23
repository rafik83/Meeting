<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Event;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

class GetFutureEventsOrEventsWithoutDaysByAdminQuery
{
    /** @var Admin */
    public $admin;

    /** @var Event */
    public $excludedEvent;

    public function __construct(Admin $admin, Event $excludedEvent)
    {
        $this->admin = $admin;
        $this->excludedEvent = $excludedEvent;
    }
}
