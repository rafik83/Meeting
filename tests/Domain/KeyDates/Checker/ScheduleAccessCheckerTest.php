<?php

namespace Proximum\Vimeet\Tests\Domain\KeyDates\Checker;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\KeyDates\Checker\ScheduleAccessChecker;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class ScheduleAccessCheckerTest extends TestCase
{
    public function testAllowedToAccessFalseAsDateIsNull()
    {
        $date  = new \DateTime();
        $event = EventFactory::createEvent();

        $scheduleAccessChecker = new ScheduleAccessChecker($date);
        $this->assertEquals(false, $scheduleAccessChecker->allowedToAccess($event));
    }

    public function testAllowedToAccessFalseAsDateIsInTheFuture()
    {
        $date         = new \DateTime('2016-09-12 10:10');
        $dateSchedule = new \DateTime('2016-10-12 10:10');
        $event        = EventFactory::createEvent();
        $event->getConfiguration()->setDates(null, null, $dateSchedule);

        $scheduleAccessChecker = new ScheduleAccessChecker($date);
        $this->assertEquals(false, $scheduleAccessChecker->allowedToAccess($event));
    }

    public function testAllowedToAccessTrue()
    {
        $date         = new \DateTime('2016-10-14 10:10');
        $dateSchedule = new \DateTime('2016-10-12 10:10');
        $event        = EventFactory::createEvent();
        $event->getConfiguration()->setDates(null, null, $dateSchedule);

        $scheduleAccessChecker = new ScheduleAccessChecker($date);
        $this->assertEquals(true, $scheduleAccessChecker->allowedToAccess($event));
    }
}
