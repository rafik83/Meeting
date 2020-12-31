<?php

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
        $dateFormatter = \IntlDateFormatter::create(
            $locale ?: self::DEFAULT_LOCALE,
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::SHORT,
            $timeZone
        );

        return self::getStringOutOfFormat($dateFormatter, $datetime);
    }

    /**
     * @param \DateTimeInterface $datetime
     * @param string             $locale
     * @param string             $timeZone
     *
     * @return string
     */
    public static function getDay(\DateTimeInterface $datetime, string $locale, string $timeZone)
    {
        $dateFormatter = \IntlDateFormatter::create(
            $locale,
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::NONE,
            $timeZone
        );

        return self::getStringOutOfFormat($dateFormatter, $datetime);
    }

    /**
     * @param \IntlDateFormatter $formatter
     * @param \DateTimeInterface $datetime
     *
     * @return string
     */
    private static function getStringOutOfFormat(\IntlDateFormatter $formatter, \DateTimeInterface $datetime)
    {
        $formatted = $formatter->format($datetime);

        return false !== $formatted ? $formatted : '';
    }
}
