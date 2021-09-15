<?php

namespace Proximum\Vimeet\Tests\Application\Query\Planner;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Exception\Planner\DayNotConfiguredException;
use Proximum\Vimeet\Application\Query\Planner\DayViewQuery;
use Proximum\Vimeet\Application\Query\Planner\DayViewQueryHandler;
use Proximum\Vimeet\Application\View\Planner\Day;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class DayViewQueryHandlerTest extends TestCase
{
    public function testHandleNoDayException()
    {
        $this->expectException(DayNotConfiguredException::class);
        $dayViewQueryHandler = new DayViewQueryHandler();
        $dayViewQueryHandler->handle(new DayViewQuery([]));
    }

    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $day1  = new Event\Day(
            $event,
            new \DateTime('2016-10-12 10:00:00'),
            new \DateTime('2016-10-12 18:30:00')
        );
        $day2  = new Event\Day(
            $event,
            new \DateTime('2016-10-13 11:00:00'),
            new \DateTime('2016-10-13 19:45:00')
        );

        $reflection = new \ReflectionClass(Event\Day::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($day1, 1);
        $property->setValue($day2, 2);
        $property->setAccessible(false);

        $dayViewQueryHandler = new DayViewQueryHandler();
        $result = $dayViewQueryHandler->handle(new DayViewQuery([$day1, $day2]));

        $expected = [
            new Day(1, 12, 10, 2016),
            new Day(2, 13, 10, 2016),
        ];

        $this->assertEquals($expected, $result);
    }
}
