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
}
