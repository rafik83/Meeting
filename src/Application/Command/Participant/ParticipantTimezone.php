<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Participant;

class ParticipantTimezone implements Command
{
    /** @var Participant */
    public $participant;

    /** @var null|string */
    public $timezone;

    public function __construct(Participant $participant, ?string $timezone)
    {
        $this->participant = $participant;
        $this->timezone = $timezone;
    }
}
