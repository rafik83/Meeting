<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Factory;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Invoice\Prefix;

class EventFactory
{
    const FALLBACK_LOCALE_DEFAULT = 'fr';

    /**
     * @param string|null $eventTitle
     * @param string      $fallbackLocale
     * @param string[]    $locales
     * @param string      $vatMode
     * @param null|Event  $duplicatedFrom
     *
     * @return Event
     */
    public static function createEvent(
        $eventTitle = null,
        $fallbackLocale = self::FALLBACK_LOCALE_DEFAULT,
        array $locales = ['fr', 'en'],
        $vatMode = Event::VAT_MODE_ET,
        Event $duplicatedFrom = null
    ) {
        $prefix = self::createInvoicePrefix();

        return new Event(
            null === $eventTitle ? 'super event' : $eventTitle,
            $fallbackLocale,
            $locales,
            $vatMode,
            20,
            'FR',
            'EUR',
            'Europe/Paris',
            'super-event.vimeet.proximum',
            'proximum',
            'team-project@example.net',
            $prefix,
            true,
            $duplicatedFrom
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
