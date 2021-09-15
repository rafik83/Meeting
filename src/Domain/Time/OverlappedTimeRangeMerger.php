<?php

namespace Proximum\Vimeet\Domain\Time;

class OverlappedTimeRangeMerger
{
    /**
     * @param AbstractTimeRange[] $timeRanges
     *
     * @return AbstractTimeRange[]
     */
    public function merge(array $timeRanges): array
    {
        if (\count($timeRanges) <= 1) {
            return $timeRanges;
        }

        // We need to sort the time ranges first
        // To avoid having a first time range created which begins at 10:00 and ends at 13:00
        // Then another which begins at 14:00 and ends at 15:00
        // And another one which begins at 12:00 at ends at 14:30
        // Which would result in a two time range instead of one
        usort($timeRanges, function (AbstractTimeRange $first, AbstractTimeRange $second) {
            return $first->getBegin() > $second->getBegin();
        });

        $timeRangesMerged = [];

        foreach ($timeRanges as $timeRange) {
            if (empty($timeRangesMerged)) {
                $timeRangesMerged[] = $timeRange;

                continue;
            }

            $overlapped = false;

            foreach ($timeRangesMerged as $timeRangeMerged) {
                if (TimeOverlap::overlap($timeRangeMerged, $timeRange)
                    || TimeOverlap::touch($timeRangeMerged, $timeRange)
                ) {
                    $overlapped = true;
                    $timeRangeMerged->merge($timeRange);
                    break;
                }
            }

            if (false === $overlapped) {
                $timeRangesMerged[] = $timeRange;
            }
        }

        return $timeRangesMerged;
    }
}
