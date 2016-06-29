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
     * @return Event
     */
    public static function createEvent()
    {
        return new Event(
            'super event',
            'fr',
            ['fr', 'en'],
            Event::VAT_MODE_ATI,
            20,
            'FR',
            'EUR',
            'Europe/Paris',
            'super-event.vimeet.proximum.dev',
            'proximum'
        );
    }
}
