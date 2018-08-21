<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Meeting\Admin\ListMeeting;

use Proximum\Vimeet\Domain\Model\Participant;

class ParticipantViewQuery
{
    /** @var Participant */
    public $participant;

    /** @var string */
    public $locale;

    public function __construct(Participant $participant, string $locale)
    {
        $this->participant = $participant;
        $this->locale = $locale;
    }
}
