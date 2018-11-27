<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Participant\Sheet;

class ParticipantListView
{
    /** @var ParticipantView */
    public $currentParticipant;

    /** @var ParticipantView[] */
    public $participants;

    public function __construct(ParticipantView $currentParticipant, array $participants)
    {
        $this->currentParticipant = $currentParticipant;
        $this->participants = $participants;
    }

    public function hasMoreThanOneParticipant(): bool
    {
        return \count($this->participants) > 1;
    }
}
