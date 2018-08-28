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

class GetTimezoneHelper
{
    public static function getTimezoneByEventAndParticipant(Event $event, Participant $participant): string
    {
        if ($participant->isVisio() && $participant->getTimezone()) {
            return $participant->getTimezone();
        }

        return $event->getTimeZone();
    }
}
