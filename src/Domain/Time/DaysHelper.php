<?php

namespace Proximum\Vimeet\Domain\Time;

class DaysHelper
{
    /**
     * @param TimeRangeView[] $timeRangeViews
     *
     * @return TimeRangeView[]
     */
    public static function splitDays(array $timeRangeViews): array
    {
        $splitedDays = self::splitOneDayIntoTwoDays($timeRangeViews);

        return self::mergeDays($splitedDays);
    }

    /**
     * @param TimeRangeView[] $timeRangeViews
     *
     * @return TimeRangeView[]
     */
    public static function mergeDays(array $timeRangeViews): array
    {
        $previousDay = null;
        $mergeDays = [];

        foreach ($timeRangeViews as $day) {
            if ($previousDay instanceof TimeRangeView) {
                if ($previousDay->getBegin()->format('Y-m-d') === $day->getEnd()->format('Y-m-d')) {
                    $previousDay->merge($day);

                    continue;
                } else {
                    $mergeDays[] = $previousDay;
                }
            }

            $previousDay = $day;
        }

        $mergeDays[] = $previousDay;

        return $mergeDays;
    }

    /**
     * @param TimeRangeView[] $timeRangeViews
     *
     * @return TimeRangeView[]
     */
    public static function splitOneDayIntoTwoDays(array $timeRangeViews): array
    {
        $splitedDays = [];

        foreach ($timeRangeViews as $timeRange) {
            if ($timeRange->getBegin()->format('Y-m-d') !== $timeRange->getEnd()->format('Y-m-d')) {
                $splitedDays[] = new TimeRangeView(
                    self::cloneDateTime($timeRange->getBegin()),
                    self::cloneDateTime($timeRange->getBegin())->setTime(23, 59, 59)
                );

                $splitedDays[] = new TimeRangeView(
                    self::cloneDateTime($timeRange->getEnd())->setTime(0, 0),
                    self::cloneDateTime($timeRange->getEnd())
                );
            } else {
                $splitedDays[] = $timeRange;
            }
        }

        return $splitedDays;
    }

    public static function cloneDateTime(\DateTimeInterface $dateTime, ?string $timezone = null): \DateTime
    {
        return (new \DateTime())
            ->setTimestamp($dateTime->getTimestamp())
            ->setTimezone(null !== $timezone ? new \DateTimeZone($timezone) : $dateTime->getTimezone())
        ;
    }
}
