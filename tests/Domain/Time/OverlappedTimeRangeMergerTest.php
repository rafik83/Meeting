<?php

namespace Proximum\Vimeet\Tests\Domain\Time;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Time\OverlappedTimeRangeMerger;
use Proximum\Vimeet\Domain\Time\TimeRangeView;

class OverlappedTimeRangeMergerTest extends TestCase
{
    public function testMerge(): void
    {
        $timeRange1 = new TimeRangeView(
            new \DateTime('2017-10-10 10:00:00.000'),
            new \DateTime('2017-10-10 12:00:00.000')
        );

        $timeRange2 = new TimeRangeView(
            new \DateTime('2017-10-10 11:00:00.000'),
            new \DateTime('2017-10-10 13:00:00.000')
        );

        $timeRange3 = new TimeRangeView(
            new \DateTime('2017-10-10 11:00:00.000'),
            new \DateTime('2017-10-10 11:30:00.000')
        );

        $timeRange4 = new TimeRangeView(
            new \DateTime('2017-10-10 14:00:00.000'),
            new \DateTime('2017-10-10 15:00:00.000')
        );
        $timeRange5 = new TimeRangeView(
            new \DateTime('2017-10-10 14:30:00.000'),
            new \DateTime('2017-10-10 15:30:00.000')
        );

        $timeRange6 = new TimeRangeView(
            new \DateTime('2017-10-10 18:00:00.000'),
            new \DateTime('2017-10-10 19:00:00.000')
        );

        $timeRange7 = new TimeRangeView(
            new \DateTime('2017-10-10 09:10:00.000'),
            new \DateTime('2017-10-10 10:00:00.000')
        );

        $timeRange8 = new TimeRangeView(
            new \DateTime('2017-10-10 09:00:00.000'),
            new \DateTime('2017-10-10 09:30:00.000')
        );

        $timeRange9 = new TimeRangeView(
            new \DateTime('2017-10-10 20:10:00.000'),
            new \DateTime('2017-10-10 22:00:00.000')
        );
        $timeRange10 = new TimeRangeView(
            new \DateTime('2017-10-10 21:00:00.000'),
            new \DateTime('2017-10-10 21:10:00.000')
        );
        $timeRange11 = new TimeRangeView(
            new \DateTime('2017-10-10 22:00:00.000'),
            new \DateTime('2017-10-10 23:00:00.000')
        );
        $timeRange12 = new TimeRangeView(
            new \DateTime('2017-10-10 23:30:00.000'),
            new \DateTime('2017-10-10 23:45:00.000')
        );
        $timeRanges = [
            $timeRange1,
            $timeRange2,
            $timeRange3,
            $timeRange4,
            $timeRange5,
            $timeRange6,
            $timeRange7,
            $timeRange8,
            $timeRange9,
            $timeRange10,
            $timeRange11,
            $timeRange12,
        ];

        $merger = new OverlappedTimeRangeMerger();
        $result = $merger->merge($timeRanges);

        $expected = [
            new TimeRangeView(
                new \DateTime('2017-10-10 09:00:00.000'),
                new \DateTime('2017-10-10 13:00:00.000')
            ),
            new TimeRangeView(
                new \DateTime('2017-10-10 14:00:00.000'),
                new \DateTime('2017-10-10 15:30:00.000')
            ),
            new TimeRangeView(
                new \DateTime('2017-10-10 18:00:00.000'),
                new \DateTime('2017-10-10 19:00:00.000')
            ),
            new TimeRangeView(
                new \DateTime('2017-10-10 20:10:00.000'),
                new \DateTime('2017-10-10 23:00:00.000')
            ),
            new TimeRangeView(
                new \DateTime('2017-10-10 23:30:00.000'),
                new \DateTime('2017-10-10 23:45:00.000')
            )
        ];

        $this->assertEquals($expected, $result);
    }
}
