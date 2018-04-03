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

/**
 * This class is used to truncate a timeRange that overlaps other array of timeRanges
 * The timeRange is cut of the part that overlaps the other timeRange
 */
class OverlappedTimeRangeTruncater
{
    /**
     * @param AbstractTimeRange   $needle
     * @param AbstractTimeRange[] $haystack
     *
     * @return AbstractTimeRange[]
     */
    public function truncate(AbstractTimeRange $needle, array $haystack): array
    {
        $result = [];
        $needleToCheck = $needle;

        foreach ($haystack as $timeRange) {
            if (TimeOverlap::beginIn($needleToCheck, $timeRange)) {
                $needleToCheck->begin = $timeRange->end;

                continue;
            }

            if (TimeOverlap::endIn($needleToCheck, $timeRange)) {
                $needleToCheck->end = $timeRange->begin;

                continue;
            }

            // If the needle contains a timeRange
            // We can the needle in two, and continue the check with the second part
            if (TimeOverlap::contains($timeRange, $needleToCheck)) {
                $firstNeedle = clone $needleToCheck;
                $firstNeedle->end = $timeRange->begin;

                $result[] = $firstNeedle;

                $secondNeedle = clone $needleToCheck;
                $secondNeedle->begin = $timeRange->end;
                $needleToCheck = $secondNeedle;
            }
        }

        $result[] = $needleToCheck;

        return $result;
    }
}
