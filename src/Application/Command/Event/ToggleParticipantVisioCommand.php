<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;

class ToggleParticipantVisioCommand implements Command
{
    /** @var Event */
    public $event;

    /** @var int */
    public $visio;

    public function __construct(Event $event, int $visio)
    {
        $this->event = $event;
        $this->visio = $visio;
    }
}
