<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda\Meeting;

use Proximum\Vimeet\Application\View\Participant\CardView;

class MeetingParticipantView
{
    /**
     * @var CardView
     */
    public $card;

    /**
     * @param CardView $cardView
     */
    public function __construct(CardView $cardView)
    {
        $this->card = $cardView;
    }
}
