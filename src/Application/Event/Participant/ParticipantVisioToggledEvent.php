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

class ParticipantVisioToggledEvent extends Event
{
    /** @var Participant */
    public $participant;

    /** @var bool */
    public $isVisio;

    public function __construct(Participant $participant, bool $isVisio)
    {
        $this->participant = $participant;
        $this->isVisio = $isVisio;
    }
}
