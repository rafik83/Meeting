<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Domain\Model\Participant;

class ParticipantViewQuery
{
    /**
     * @var string
     */
    public $locale;

    /**
     * @var Participant[]
     */
    public $participants;

    /**
     * @param Participant[] $participants
     * @param string        $locale
     */
    public function __construct(array $participants, $locale)
    {
        $this->participants = $participants;
        $this->locale       = $locale;
    }
}
