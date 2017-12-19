<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\User\Phone;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;

class IgnoreConfirmation
{
    /** @var Event */
    public $event;

    /** @var Participant */
    public $participant;

    /**
     * @param Event       $event
     * @param Participant $participant
     */
    public function __construct(Event $event, Participant $participant)
    {
        $this->event       = $event;
        $this->participant = $participant;
    }
}
