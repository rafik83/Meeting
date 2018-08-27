<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;

class ParticipantPresenceCommand implements Command
{
    /** @var Participant */
    public $participant;

    /** @var Meeting */
    public $meeting;

    public function __construct(Participant $participant, Meeting $meeting)
    {
        $this->participant = $participant;
        $this->meeting = $meeting;
    }
}
