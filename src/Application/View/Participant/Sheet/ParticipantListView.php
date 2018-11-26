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
    public $otherParticipants;

    public function __construct(ParticipantView $currentParticipant, array $otherParticipants)
    {
        $this->currentParticipant = $currentParticipant;
        $this->otherParticipants = $otherParticipants;
    }
}
