<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Unavailability\SystemGenerator;

use Proximum\Vimeet\Domain\Time\TimeOverlap;

class OverlappedTimeRangeMerger
{
    /**
     * @param AbstractTimeRange[] $timeRanges
     *
     * @return AbstractTimeRange[]
     */
    public function merge(array $timeRanges): array
    {
        // We need to sort the time ranges first
        // To avoid having a first time range created which begins at 10:00 and ends at 13:00
        // Then another which begins at 14:00 and ends at 15:00
        // And another one which begins at 12:00 at ends at 14:30
        // Which would result in a two time range instead of one
        usort($timeRanges, function(AbstractTimeRange $first, AbstractTimeRange $second) {
            return $first->getBegin() > $second->getBegin();
        });

        $timeRangesCollapsed = [];

        foreach ($timeRanges as $timeRange) {
            if (empty($timeRangesCollapsed)) {
                $timeRangesCollapsed[] = $timeRange;

                continue;
            }

            $overlapped = false;

            foreach ($timeRangesCollapsed as $timeRangeCollapsed) {
                if (TimeOverlap::overlap($timeRangeCollapsed, $timeRange)
                    || $timeRangeCollapsed->getEnd() == $timeRange->getBegin()
                    || $timeRangeCollapsed->getBegin() == $timeRange->getEnd()
                ) {
                    $overlapped = true;
                    $timeRangeCollapsed->merge($timeRange);
                    break;
                }
            }

            if (false === $overlapped) {
                $timeRangesCollapsed[] = $timeRange;
            }
        }

        return $timeRangesCollapsed;
    }
}
