<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Unavailability\SystemGenerator;

use Proximum\Vimeet\Domain\Time\AbstractTimeRange;
use Proximum\Vimeet\Domain\Time\TimeOverlap;

/**
 * This class is used to truncate a timeRange that overlaps other array of timeRanges
 * The timeRange is cut of the part that overlaps the other timeRange
 */
class OverlappedTimeRangeTruncater
{
    /**
     * @param AbstractTimeRange   $unavailability
     * @param AbstractTimeRange[] $activatedTimeRanges
     *
     * @return AbstractTimeRange[]
     */
    public function truncate(AbstractTimeRange $unavailability, array $activatedTimeRanges): array
    {
        $result = [];
        $unavailabilityToCheck = $unavailability;

        foreach ($activatedTimeRanges as $activatedTimeRange) {
            if (TimeOverlap::contains($unavailabilityToCheck, $activatedTimeRange)) {
                return $result;
            }

            if (TimeOverlap::beginIn($unavailabilityToCheck, $activatedTimeRange)) {
                $unavailabilityToCheck->begin = $activatedTimeRange->end;

                if ($unavailabilityToCheck->end < $unavailabilityToCheck->begin) {
                    $unavailabilityToCheck->end = $unavailabilityToCheck->begin;
                }

                continue;
            }

            if (TimeOverlap::endIn($unavailabilityToCheck, $activatedTimeRange)) {
                $unavailabilityToCheck->end = $activatedTimeRange->begin;

                if ($unavailabilityToCheck->end < $unavailabilityToCheck->begin) {
                    $unavailabilityToCheck->begin = $unavailabilityToCheck->end;
                }

                continue;
            }

            if (TimeOverlap::contains($activatedTimeRange, $unavailabilityToCheck)) {
                $firstNeedle = clone $unavailabilityToCheck;
                $firstNeedle->end = $activatedTimeRange->begin;

                if ($firstNeedle->end > $firstNeedle->begin && $firstNeedle->getBegin() != $firstNeedle->getEnd()) {
                    $result[] = $firstNeedle;
                }

                $secondNeedle = clone $unavailabilityToCheck;
                $secondNeedle->begin = $activatedTimeRange->end;
                $unavailabilityToCheck = $secondNeedle;
            }

            // If the needle is cut to nothing
            if ($unavailabilityToCheck->getBegin() == $unavailabilityToCheck->getEnd()) {
                return $result;
            }
        }

        $result[] = $unavailabilityToCheck;

        return $result;
    }
}
