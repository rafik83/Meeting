<?php

namespace Proximum\Vimeet\Tests\Domain\Time;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Time\OverlappedTimeRangeTruncater;
use Proximum\Vimeet\Domain\Time\TimeRangeNotAccessibleView;
use Proximum\Vimeet\Domain\Time\TimeRangeView;

class OverlappedTimeRangeTruncaterTest extends TestCase
{
    public function testTruncateWithEndIn()
    {
        $timeRanges = [
            new TimeRangeView(
                new \DateTime('2017-10-10 10:00:00.000'),
                new \DateTime('2017-10-10 12:00:00.000')
            ),
            new TimeRangeView(
                new \DateTime('2017-10-10 14:00:00.000'),
                new \DateTime('2017-10-10 17:00:00.000')
            ),
        ];

        $needle = new TimeRangeNotAccessibleView(
            new \DateTime('2017-10-10 09:00:00.000'),
            new \DateTime('2017-10-10 11:00:00.000')
        );

        $truncater = new OverlappedTimeRangeTruncater();
        $result = $truncater->truncate($needle, $timeRanges);

        $expected = [
            new TimeRangeNotAccessibleView(
                new \DateTime('2017-10-10 09:00:00.000'),
                new \DateTime('2017-10-10 10:00:00.000')
            ),
        ];

        $this->assertEquals($expected, $result);
    }

    public function testTruncateWithBeginIn()
    {
        $timeRanges = [
            new TimeRangeView(
                new \DateTime('2017-10-10 10:00:00.000'),
                new \DateTime('2017-10-10 12:00:00.000')
            ),
            new TimeRangeView(
                new \DateTime('2017-10-10 14:00:00.000'),
                new \DateTime('2017-10-10 17:00:00.000')
            ),
        ];

        $needle = new TimeRangeNotAccessibleView(
            new \DateTime('2017-10-10 10:00:00.000'),
            new \DateTime('2017-10-10 13:00:00.000')
        );

        $truncater = new OverlappedTimeRangeTruncater();
        $result = $truncater->truncate($needle, $timeRanges);

        $expected = [
            new TimeRangeNotAccessibleView(
                new \DateTime('2017-10-10 12:00:00.000'),
                new \DateTime('2017-10-10 13:00:00.000')
            ),
        ];

        $this->assertEquals($expected, $result);
    }

    public function testTruncateWithContains()
    {
        $timeRanges = [
            new TimeRangeView(
                new \DateTime('2017-10-10 10:00:00.000'),
                new \DateTime('2017-10-10 12:00:00.000')
            ),
            new TimeRangeView(
                new \DateTime('2017-10-10 14:00:00.000'),
                new \DateTime('2017-10-10 17:00:00.000')
            ),
        ];

        $needle = new TimeRangeNotAccessibleView(
            new \DateTime('2017-10-10 09:00:00.000'),
            new \DateTime('2017-10-10 19:00:00.000')
        );

        $truncater = new OverlappedTimeRangeTruncater();
        $result = $truncater->truncate($needle, $timeRanges);

        $expected = [
            new TimeRangeNotAccessibleView(
                new \DateTime('2017-10-10 09:00:00.000'),
                new \DateTime('2017-10-10 10:00:00.000')
            ),
            new TimeRangeNotAccessibleView(
                new \DateTime('2017-10-10 12:00:00.000'),
                new \DateTime('2017-10-10 14:00:00.000')
            ),
            new TimeRangeNotAccessibleView(
                new \DateTime('2017-10-10 17:00:00.000'),
                new \DateTime('2017-10-10 19:00:00.000')
            ),
        ];

        $this->assertEquals($expected, $result);
    }

    public function testTruncateWithTimeRangeInside()
    {
        $timeRanges = [
            new TimeRangeView(
                new \DateTime('2017-10-10 09:00:00.000'),
                new \DateTime('2017-10-10 12:00:00.000')
            ),
            new TimeRangeView(
                new \DateTime('2017-10-10 14:00:00.000'),
                new \DateTime('2017-10-10 17:00:00.000')
            ),
        ];

        $needle = new TimeRangeNotAccessibleView(
            new \DateTime('2017-10-10 10:00:00.000'),
            new \DateTime('2017-10-10 12:00:00.000')
        );

        $truncater = new OverlappedTimeRangeTruncater();
        $result = $truncater->truncate($needle, $timeRanges);

        $expected = [];

        $this->assertEquals($expected, $result);
    }

    public function testTruncateWithTimeRangeOverlapEntirely(): void
    {
        $timeRanges = [
            new TimeRangeView(
                new \DateTime('2017-10-10 11:00:00.000'),
                new \DateTime('2017-10-10 20:00:00.000')
            ),
        ];

        $needle = new TimeRangeNotAccessibleView(
            new \DateTime('2017-10-10 11:00:00.000'),
            new \DateTime('2017-10-10 13:00:00.000')
        );

        $truncater = new OverlappedTimeRangeTruncater();
        $result = $truncater->truncate($needle, $timeRanges);

        $expected = [];

        $this->assertEquals($expected, $result);
    }

    public function testTruncateWithTimeRangeOverlapEntirelyAfternoon(): void
    {
        $timeRanges = [
            new TimeRangeView(
                new \DateTime('2017-10-10 11:00:00.000'),
                new \DateTime('2017-10-10 20:00:00.000')
            ),
        ];

        $needle = new TimeRangeNotAccessibleView(
            new \DateTime('2017-10-10 14:00:00.000'),
            new \DateTime('2017-10-10 20:00:00.000')
        );

        $truncater = new OverlappedTimeRangeTruncater();
        $result = $truncater->truncate($needle, $timeRanges);

        $expected = [];

        $this->assertEquals($expected, $result);
    }
}
