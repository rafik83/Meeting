<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Meeting;

use Proximum\Vimeet\Domain\Model\Participant;

class ParticipantsViewQuery
{
    /**
     * @var Participant[]
     */
    public $participants;

    /**
     * @var string
     */
    public $locale;

    /**
     * ParticipantsViewQuery constructor.
     *
     * @param Participant[] $participants
     * @param string        $locale
     */
    public function __construct(array $participants, $locale)
    {
        $this->participants = $participants;
        $this->locale       = $locale;
    }
}
