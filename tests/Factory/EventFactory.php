<?php

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
        ?string $eventTitle = null,
        ?string $fallbackLocale = null,
        array $locales = ['fr', 'en'],
        ?string $vatMode = null,
        Event $duplicatedFrom = null,
        int $id = 1
    ) {
        if (null === $vatMode) {
            $vatMode = Event::VAT_MODE_ET;
        }

        if (null === $fallbackLocale) {
            $fallbackLocale = self::FALLBACK_LOCALE_DEFAULT;
        }

        $prefix = self::createInvoicePrefix();

        $event = new Event(
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

        $reflection = new \ReflectionClass(Event::class);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($event, $id);
        $property->setAccessible(false);

        return $event;
    }

    /**
     * @return Prefix
     */
    public static function createInvoicePrefix()
    {
        return new Prefix('Vimeet', 'Vi');
    }
}
