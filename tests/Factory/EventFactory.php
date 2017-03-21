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
use Proximum\Vimeet\Domain\Model\Invoice\Prefix;

class EventFactory
{
    const FALLBACK_DEFAULT = 'fr';

    /**
     * @param string|null $eventTitle
     *
     * @param string      $fallback
     *
     * @return Event
     */
    public static function createEvent($eventTitle = null, $fallback = self::FALLBACK_DEFAULT)
    {
        $prefix = self::createInvoicePrefix();

        return new Event(
            null === $eventTitle ? 'super event' : $eventTitle,
            $fallback,
            ['fr', 'en'],
            Event::VAT_MODE_ATI,
            20,
            'FR',
            'EUR',
            'Europe/Paris',
            'super-event.vimeet.proximum.dev',
            'proximum',
            'team-project@example.net',
            $prefix
        );
    }

    /**
     * @return Prefix
     */
    public static function createInvoicePrefix()
    {
        return new Prefix('Vimeet', 'Vi');
    }
}
