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
use Proximum\Vimeet\Domain\Model\Tip\Tip;

class TipFactory
{
    const ON_MEETING_MANAGEMENT = 'onMeetingManagement';
    const ON_CATALOG            = 'onCatalog';
    const ON_PRINT_PLANNING     = 'onPrintPlanning';
    const ON_SHEET              = 'onSheet';
    const ON_AGENDA             = 'onAgenda';
    const ON_PACKAGE            = 'onPackage';
    const ON_CONTACTS           = 'onContacts';
    const ON_PROGRAM            = 'onProgram';
    const ON_CONFIRMATION_PHONE = 'onConfirmationPhone';
    const ON_NETWORKING = 'onNetworking';

    const LOCALES = ['fr', 'en'];

    /**
     * @param string     $tipTitle
     * @param Event|null $event
     * @param array      $pages
     * @param array      $locales
     *
     * @return Tip
     */
    public static function createTip(
        $tipTitle,
        Event $event = null,
        array $pages = [
            self::ON_MEETING_MANAGEMENT => true,
            self::ON_CATALOG            => true,
            self::ON_PRINT_PLANNING     => true,
            self::ON_AGENDA             => true,
            self::ON_PACKAGE            => true,
            self::ON_CONTACTS           => true,
            self::ON_SHEET              => true,
            self::ON_PROGRAM            => true,
            self::ON_CONFIRMATION_PHONE => true,
            self::ON_NETWORKING         => true,
        ],
        array $locales = self::LOCALES
    ) {
        $dateTime = new \DateTime();

        $tip = new Tip(
            $tipTitle,
            $event,
            isset($pages[self::ON_MEETING_MANAGEMENT]) ? $pages[self::ON_MEETING_MANAGEMENT] : false,
            isset($pages[self::ON_CATALOG]) ? $pages[self::ON_CATALOG] : false,
            isset($pages[self::ON_PRINT_PLANNING]) ? $pages[self::ON_PRINT_PLANNING] : false,
            isset($pages[self::ON_SHEET]) ? $pages[self::ON_SHEET] : false,
            isset($pages[self::ON_AGENDA]) ? $pages[self::ON_AGENDA] : false,
            isset($pages[self::ON_PACKAGE]) ? $pages[self::ON_PACKAGE] : false,
            isset($pages[self::ON_CONTACTS]) ? $pages[self::ON_CONTACTS] : false,
            isset($pages[self::ON_PROGRAM]) ? $pages[self::ON_PROGRAM] : false,
            isset($pages[self::ON_CONFIRMATION_PHONE]) ? $pages[self::ON_CONFIRMATION_PHONE] : false,
            isset($pages[self::ON_NETWORKING]) ? $pages[self::ON_NETWORKING] : false,
            $dateTime
        );

        foreach ($locales as $locale) {
            $tip->setTranslation($locale, $tipTitle . ' (' . $locale . ')', 'content_' . $locale, $dateTime);
        }

        return $tip;
    }
}
