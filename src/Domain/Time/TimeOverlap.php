<?php

namespace Proximum\Vimeet\Domain\Time;

class TimeOverlap
{
    /**
     * @param TimeRangeInterface $needle
     * @param TimeRangeInterface $haystack
     *
     * @return bool
     */
    public static function contains(TimeRangeInterface $needle, TimeRangeInterface $haystack): bool
    {
        return $haystack->getBegin() <= $needle->getBegin() && $needle->getEnd() <= $haystack->getEnd();
    }

    /**
     * @param TimeRangeInterface $needle
     * @param TimeRangeInterface $haystack
     *
     * @return bool
     */
    public static function beginIn(TimeRangeInterface $needle, TimeRangeInterface $haystack): bool
    {
        return $haystack->getBegin() <= $needle->getBegin() && $needle->getBegin() < $haystack->getEnd();
    }

    /**
     * @param TimeRangeInterface $needle
     * @param TimeRangeInterface $haystack
     *
     * @return bool
     */
    public static function endIn(TimeRangeInterface $needle, TimeRangeInterface $haystack): bool
    {
        return $haystack->getBegin() < $needle->getEnd() && $needle->getEnd() <= $haystack->getEnd();
    }

    /**
     * @param TimeRangeInterface $one
     * @param TimeRangeInterface $another
     *
     * @return bool
     */
    public static function overlap(TimeRangeInterface $one, TimeRangeInterface $another): bool
    {
        return self::beginIn($one, $another)
            || self::endIn($one, $another)
            || self::contains($another, $one)
        ;
    }

    /**
     * @param \DateTimeInterface $threshold
     * @param \DateTimeInterface $dateTime
     *
     * @return \DateTimeInterface
     */
    public static function floor(\DateTimeInterface $threshold, \DateTimeInterface $dateTime): \DateTimeInterface
    {
        if ($dateTime > $threshold) {
            return $threshold;
        }

        return $dateTime;
    }

    /**
     * @param \DateTimeInterface $ceiling
     * @param \DateTimeInterface $dateTime
     *
     * @return \DateTimeInterface
     */
    public static function ceil(\DateTimeInterface $ceiling, \DateTimeInterface $dateTime): \DateTimeInterface
    {
        if ($dateTime < $ceiling) {
            return $ceiling;
        }

        return $dateTime;
    }

    /**
     * @param TimeRangeInterface $one
     * @param TimeRangeInterface $another
     *
     * @return bool
     */
    public static function touch(TimeRangeInterface $one, TimeRangeInterface $another): bool
    {
        return $one->getEnd() == $another->getBegin()
            || $one->getBegin() == $another->getEnd();
    }
}
