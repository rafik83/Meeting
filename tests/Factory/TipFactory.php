<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Factory;

use Proximum\Vimeet\Domain\Model\Tip\Tip;

class TipFactory
{
    const ON_MEETING_MANAGEMENT = 'onMeetingManagement';
    const ON_CATALOG            = 'onCatalog';
    const ON_PRINT_PLANNING     = 'onPrintPlanning';
    const ON_SHEET              = 'onSheet';
    const ON_AGENDA             = 'onAgenda';
    const ON_PROGRAM            = 'onProgram';

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
            self::ON_PRINT_PLANNING     => true,
            self::ON_AGENDA             => true,
            self::ON_SHEET              => true,
            self::ON_PROGRAM            => true,
        ]
    ) {
        $tip = new Tip(
            $tipTitle,
            $pages[self::ON_MEETING_MANAGEMENT],
            $pages[self::ON_CATALOG],
            $pages[self::ON_PRINT_PLANNING],
            $pages[self::ON_SHEET],
            $pages[self::ON_AGENDA],
            $pages[self::ON_PROGRAM],
            new \DateTime()
        );

        foreach (self::LOCALES as $locale) {
            $tip->setTranslation($locale, 'title_' . $locale, 'content_' . $locale);
        }

        return $tip;
    }
}
