<?php

namespace Proximum\Vimeet\Domain\Time;

use Proximum\Vimeet\Domain\Model\Event;

class TimeRangeViewTransformer
{
    /**
     * @param Event\Day[] $eventDays
     * @param string      $timeZone
     *
     * @return array
     */
    public static function fromEventDays(array $eventDays, string $timeZone): array
    {
        $timeZonedTimeRangeViews = [];

        foreach ($eventDays as $day) {
            $timeZonedTimeRangeViews[] = new TimeRangeView(
                DaysHelper::cloneDateTime($day->getBegin(), $timeZone),
                DaysHelper::cloneDateTime($day->getEnd(), $timeZone)
            );
        }

        return DaysHelper::splitDays($timeZonedTimeRangeViews);
    }
}
