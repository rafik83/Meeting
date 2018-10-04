<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Participant\IsParticipantVisio;

class GetTimezoneHelper
{
    /** @var IsParticipantVisio */
    private $isParticipantVisio;

    public function __construct(IsParticipantVisio $isParticipantVisio)
    {
        $this->isParticipantVisio = $isParticipantVisio;
    }

    public function getTimezoneByEventAndParticipant(Event $event, Participant $participant): string
    {
        if ($this->isParticipantVisio->isSatisfiedBy($participant) && $participant->getTimezone()) {
            return $participant->getTimezone();
        }

        return $event->getTimeZone();
    }
}
