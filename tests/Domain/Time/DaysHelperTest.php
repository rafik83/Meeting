<?php

namespace Proximum\Vimeet\Tests\Domain\Time;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Time\DaysHelper;
use Proximum\Vimeet\Domain\Time\TimeRangeView;

class DaysHelperTest extends TestCase
{
    public function testSplitDays()
    {
        $results = DaysHelper::splitDays(
            [
                new TimeRangeView(
                    (new \DateTime('2017-11-29 09:00:00'))->setTimezone(new \DateTimeZone('Asia/Tokyo')),
                    (new \DateTime('2017-11-29 20:00:00'))->setTimezone(new \DateTimeZone('Asia/Tokyo'))
                ),
                new TimeRangeView(
                    (new \DateTime('2017-11-30 10:00:00'))->setTimezone(new \DateTimeZone('Asia/Tokyo')),
                    (new \DateTime('2017-11-30 18:00:00'))->setTimezone(new \DateTimeZone('Asia/Tokyo'))
                ),
            ]
        );

        $this->assertEquals(
            [
                new TimeRangeView(
                    (new \DateTime('2017-11-29T18:00:00+0900')),
                    (new \DateTime('2017-11-29T23:59:59+0900'))
                ),
                new TimeRangeView(
                    (new \DateTime('2017-11-30T00:00:00+0900')),
                    (new \DateTime('2017-11-30T23:59:59+0900'))
                ),
                new TimeRangeView(
                    (new \DateTime('2017-12-01T00:00:00+0900')),
                    (new \DateTime('2017-12-01T03:00:00+0900'))
                ),
            ],
            $results
        );
    }
}
