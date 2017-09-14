<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Unavailability\Mass;

use Proximum\Vimeet\Domain\Model\Event;

class Dispatcher
{
    /** @var Event */
    public $event;

    /** @var bool */
    public $launchDuringExport;

    /**
     * @param Event $event
     * @param bool  $launchDuringExport
     */
    public function __construct(Event $event, bool $launchDuringExport = false)
    {
        $this->event = $event;
        $this->launchDuringExport = $launchDuringExport;
    }
}
