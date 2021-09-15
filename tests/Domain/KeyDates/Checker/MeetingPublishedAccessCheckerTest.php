<?php

namespace Proximum\Vimeet\Tests\Domain\KeyDates\Checker;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingPublishedAccessChecker;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class MeetingPublishedAccessCheckerTest extends TestCase
{
    public function testAllowedToAccessFalseAsDateIsNull()
    {
        $date  = new \DateTime();
        $event = EventFactory::createEvent();

        $meetingPublishedAccessChecker = new MeetingPublishedAccessChecker($date);
        $this->assertEquals(false, $meetingPublishedAccessChecker->allowedToAccess($event));
    }

    public function testAllowedToAccessFalseAsDateIsInTheFuture()
    {
        $date          = new \DateTime('2016-09-12 10:10');
        $dateHappening = new \DateTime('2016-10-12 10:10');
        $event         = EventFactory::createEvent();
        $event->getConfiguration()->setDates(null, null, $dateHappening);

        $meetingPublishedAccessChecker = new MeetingPublishedAccessChecker($date);
        $this->assertEquals(false, $meetingPublishedAccessChecker->allowedToAccess($event));
    }

    public function testAllowedToAccessTrue()
    {
        $date          = new \DateTime('2016-10-14 10:10');
        $dateHappening = new \DateTime('2016-10-12 10:10');
        $event         = EventFactory::createEvent();
        $event->getConfiguration()->setDates(null, null, $dateHappening);

        $meetingPublishedAccessChecker = new MeetingPublishedAccessChecker($date);
        $this->assertEquals(true, $meetingPublishedAccessChecker->allowedToAccess($event));
    }
}
