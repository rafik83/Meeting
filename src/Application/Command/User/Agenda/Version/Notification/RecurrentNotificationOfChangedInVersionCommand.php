<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\User\Agenda\Version\Notification;

use Proximum\Vimeet\Domain\Model\Event;

class RecurrentNotificationOfChangedInVersionCommand
{
    /** @var Event[] */
    public $events;

    /** @var bool */
    public $dday;

    public function __construct(array $events, bool $dday)
    {
        $this->events = $events;
        $this->dday = $dday;
    }
}
