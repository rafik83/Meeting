<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Meeting\RequestTransformIntoMeeting;

use Proximum\Vimeet\Domain\Model\Participant;

class AvailableSlotsParticipantView
{
    /**
     * @var Participant
     */
    public $participant;

    /**
     * @var AvailableSlotView[]
     */
    public $slots = [];

    /**
     * @param Participant $participant
     * @param array       $slots
     */
    public function __construct(Participant $participant, array $slots)
    {
        $this->participant = $participant;
        $this->slots       = $slots;
    }
}
