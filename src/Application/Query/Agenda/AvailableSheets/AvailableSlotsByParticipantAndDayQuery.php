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
use Proximum\Vimeet\Domain\Time\TimeRangeInterface;

class AvailableSlotsByParticipantAndDayQuery
{
    /** @var Event */
    public $event;

    /** @var Participant */
    public $participant;

    /** @var TimeRangeInterface */
    public $day;

    public function __construct(Event $event, Participant $participant, TimeRangeInterface $day)
    {
        $this->event = $event;
        $this->participant = $participant;
        $this->day = $day;
    }
}
