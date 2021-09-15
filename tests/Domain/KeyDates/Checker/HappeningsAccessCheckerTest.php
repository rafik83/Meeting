<?php

namespace Proximum\Vimeet\Tests\Domain\KeyDates\Checker;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\KeyDates\Checker\HappeningsAccessChecker;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class HappeningsAccessCheckerTest extends TestCase
{
    public function testAllowedToAccessFalseAsDateIsNull()
    {
        $date  = new \DateTime();
        $event = EventFactory::createEvent();

        $happeningAccessChecker = new HappeningsAccessChecker($date);
        $this->assertEquals(false, $happeningAccessChecker->allowedToAccess($event));
    }

    public function testAllowedToAccessFalseAsDateIsInTheFuture()
    {
        $date          = new \DateTime('2016-09-12 10:10');
        $dateHappening = new \DateTime('2016-10-12 10:10');
        $event         = EventFactory::createEvent();
        $event->getConfiguration()->setDates(null, $dateHappening);

        $happeningAccessChecker = new HappeningsAccessChecker($date);
        $this->assertEquals(false, $happeningAccessChecker->allowedToAccess($event));
    }

    public function testAllowedToAccessTrue()
    {
        $date          = new \DateTime('2016-10-14 10:10');
        $dateHappening = new \DateTime('2016-10-12 10:10');
        $event         = EventFactory::createEvent();
        $event->getConfiguration()->setDates(null, $dateHappening);

        $happeningAccessChecker = new HappeningsAccessChecker($date);
        $this->assertEquals(true, $happeningAccessChecker->allowedToAccess($event));
    }
}
