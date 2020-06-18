<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Event\Day;

class DayHelper
{
    /**
     * This return an Intl Date Formatter that display the full day
     * (Name of the day, number, month and year) without the minute
     * In the given locale and for the given time zone
     *
     * @param string $locale
     * @param string $timeZone
     *
     * @return \IntlDateFormatter
     */
    public static function getFormatter($locale, $timeZone)
    {
        return \IntlDateFormatter::create(
            $locale,
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::NONE,
            $timeZone
        );
    }

    public static function getHourFormatter($locale, $timeZone): \IntlDateFormatter
    {
        return \IntlDateFormatter::create(
            $locale,
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::SHORT,
            $timeZone
        );
    }

    public static function getMediumHourFormatter($locale, $timeZone): \IntlDateFormatter
    {
        return \IntlDateFormatter::create(
            $locale,
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::MEDIUM,
            $timeZone
        );
    }
}
