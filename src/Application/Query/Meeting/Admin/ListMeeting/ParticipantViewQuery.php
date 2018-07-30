<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Meeting\Admin\ListMeeting;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;

class ParticipantViewQuery
{
    /** @var Participant */
    public $participant;

    /** @var Event */
    public $event;

    /** @var MeetingSlot */
    public $meetingSlot;

    /** @var string */
    public $locale;

    public function __construct(
        Participant $participant,
        Event $event,
        MeetingSlot $meetingSlot,
        string $locale
    ) {
        $this->participant = $participant;
        $this->event = $event;
        $this->meetingSlot = $meetingSlot;
        $this->locale = $locale;
    }
}
