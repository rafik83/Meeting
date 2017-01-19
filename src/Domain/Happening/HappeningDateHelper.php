<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Happening;

class HappeningDateHelper
{
    const DEFAULT_LOCALE = 'fr';

    /**
     * @param \DateTimeInterface $datetime
     * @param string|null        $locale
     * @param string             $timeZone
     *
     * @return string
     */
    public static function getHour(\DateTimeInterface $datetime, $locale, $timeZone)
    {
        if (null === $locale) {
            $locale = self::DEFAULT_LOCALE;
        }

        $dateFormatter = \IntlDateFormatter::create(
            $locale,
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::SHORT,
            $timeZone
        );

        return $dateFormatter->format($datetime);
    }

    /**
     * @param \DateTimeInterface $datetime
     * @param string             $locale
     * @param string             $timeZone
     *
     * @return string
     */
    public static function getDay(\DateTimeInterface $datetime, $locale, $timeZone)
    {
        $dateFormatter = \IntlDateFormatter::create(
            $locale,
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::NONE,
            $timeZone
        );

        return $dateFormatter->format($datetime);
    }
}
