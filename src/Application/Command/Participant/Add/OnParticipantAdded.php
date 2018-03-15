<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant\Add;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;

class OnParticipantAdded implements Command
{
    /** @var Participant */
    public $participant;

    /** @var User */
    public $adder;

    public function __construct(Participant $participant, User $adder)
    {
        $this->participant = $participant;
        $this->adder = $adder;
    }
}
