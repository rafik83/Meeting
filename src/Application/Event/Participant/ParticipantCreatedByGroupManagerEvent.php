<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event\Participant;

use Proximum\Vimeet\Domain\Model\Participant;
use Symfony\Component\EventDispatcher\Event;

class ParticipantCreatedByGroupManagerEvent extends Event
{
    /** @var Participant */
    public $participant;

    public function __construct(Participant $participant)
    {
        $this->participant = $participant;
    }
}
