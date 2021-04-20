<?php

namespace Proximum\Vimeet\Domain\Happening;

class HappeningDateHelper
{
    const DEFAULT_LOCALE = 'fr';

    /**
     * @param \DateTimeInterface $dateTime
     * @param string|null        $locale
     * @param string             $timeZone
     *
     * @return string
     */
    public static function getHour(\DateTimeInterface $dateTime, $locale, $timeZone)
    {
        $dateFormatter = \IntlDateFormatter::create(
            $locale ?: self::DEFAULT_LOCALE,
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::SHORT,
            $timeZone
        );

        return self::getStringOutOfFormat($dateFormatter, $dateTime);
    }

    /**
     * @param \DateTimeInterface $dateTime
     * @param string             $locale
     * @param string             $timeZone
     *
     * @return string
     */
    public static function getDay(\DateTimeInterface $dateTime, string $locale, string $timeZone)
    {
        $dateFormatter = \IntlDateFormatter::create(
            $locale,
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::NONE,
            $timeZone
        );

        return self::getStringOutOfFormat($dateFormatter, $dateTime);
    }

    public static function getDateTime(\DateTimeInterface $datetime, string $locale, string $timeZone): string
    {
        $dateFormatter = \IntlDateFormatter::create(
            $locale,
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::SHORT,
            $timeZone
        );

        return self::getStringOutOfFormat($dateFormatter, $datetime);
    }

    /**
     * @param \IntlDateFormatter $formatter
     * @param \DateTimeInterface $dateTime
     *
     * @return string
     */
    private static function getStringOutOfFormat(\IntlDateFormatter $formatter, \DateTimeInterface $dateTime)
    {
        $formatted = $formatter->format($dateTime);

        return false !== $formatted ? $formatted : '';
    }
}
