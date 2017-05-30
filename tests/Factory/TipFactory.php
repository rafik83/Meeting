<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Factory;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class TipFactory
{
    const ON_MEETING_MANAGEMENT = 'onMeetingManagement';
    const ON_CATALOG            = 'onCatalog';
    const ON_PRINT_PLANNING     = 'onPrintPlanning';

    const LOCALES = ['fr', 'en'];

    /**
     * @param string $tipTitle
     * @param array  $pages
     *
     * @return Tip
     */
    public static function createTip(
        $tipTitle,
        $pages = [
            self::ON_MEETING_MANAGEMENT => true,
            self::ON_CATALOG            => true,
            self::ON_PRINT_PLANNING     => true
        ]
    ) {
        $tip = new Tip(
            $tipTitle,
            $pages[self::ON_MEETING_MANAGEMENT],
            $pages[self::ON_CATALOG],
            $pages[self::ON_PRINT_PLANNING],
            new \DateTime()
        );

        foreach (self::LOCALES as $locale) {
            $tip->setTranslation($locale, 'title_' . $locale, 'content_' . $locale);
        }

        /** @var Event[] $events */
        $events = [
            EventFactory::createEvent('Event_1'),
            EventFactory::createEvent('Event_2'),
        ];

        foreach ($events as $key => $event) {
            $locale = self::LOCALES[$key];

            $type = new Type($event);
            $type->translate($locale, 'type_' . $locale, 'description_' . $locale);

            $tip->setType($type);
        }

        return $tip;
    }
}
