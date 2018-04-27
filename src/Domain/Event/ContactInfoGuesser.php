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

class ContactInfoGuesser
{
    /**
     * This method returns a string of the event contact info, separated by coma
     *
     * @param Event $event
     *
     * @return string
     */
    public static function getContactInfos(Event $event)
    {
        $contactInfos = [
            $event->getOrganiserName(),
            $event->getConfiguration()->getContactFirstName(),
            $event->getConfiguration()->getContactLastName(),
            $event->getOrganiserEmail(),
            $event->getConfiguration()->getOrganiserPhone(),
            $event->getConfiguration()->getOrganiserWebsite(),
        ];

        // Array_filter remove the possible null entries
        return implode(', ', array_filter($contactInfos));
    }
}
