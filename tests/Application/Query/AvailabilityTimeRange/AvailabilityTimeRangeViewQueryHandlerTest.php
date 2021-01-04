<?php

namespace Proximum\Vimeet\Tests\Application\Query\AvailabilityTimeRange;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\AvailabilityTimeRange\AvailabilityTimeRangeViewQuery;
use Proximum\Vimeet\Application\Query\AvailabilityTimeRange\AvailabilityTimeRangeViewQueryHandler;
use Proximum\Vimeet\Application\View\AvailabilityTimeRange\AvailabilityTimeRangeView;
use Proximum\Vimeet\Domain\Model\AvailabilityTimeRange;
use Proximum\Vimeet\Domain\Model\Event;

class AvailabilityTimeRangeViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $begin1 = new \DateTime('2017-10-10 10:00:00.000');
        $end1 = new \DateTime('2017-10-10 12:00:00.000');

        $availabilityTimeRange = new AvailabilityTimeRange($event->reveal(), 'name', $begin1, $end1);

        $handler = new AvailabilityTimeRangeViewQueryHandler();
        $result = $handler->handle(new AvailabilityTimeRangeViewQuery($availabilityTimeRange));

        $expected = new AvailabilityTimeRangeView('name', $begin1, $end1, []);

        $this->assertEquals($expected, $result);
    }
}
