<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda\AvailableSheets;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;

class AvailableSlotsByParticipantAndDayQuery
{
    /** @var Event */
    public $event;

    /** @var Participant */
    public $participant;

    /** @var Event\Day */
    public $day;

    /**
     * @param Event       $event
     * @param Participant $participant
     * @param Event\Day   $day
     */
    public function __construct(Event $event, Participant $participant, Event\Day $day)
    {
        $this->event = $event;
        $this->participant = $participant;
        $this->day = $day;
    }
}
