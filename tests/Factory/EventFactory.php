<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Factory;

use Proximum\Vimeet\Domain\Model\Event;

class EventFactory
{
    /**
     * @param null $eventTitle
     *
     * @return Event
     */
    public static function createEvent($eventTitle = null)
    {
        return new Event(
            null === $eventTitle ? 'super event' : $eventTitle,
            'fr',
            ['fr', 'en'],
            Event::VAT_MODE_ATI,
            20,
            'FR',
            'EUR',
            'Europe/Paris',
            'super-event.vimeet.proximum.dev',
            'proximum',
            'team-project@example.net'
        );
    }
}
