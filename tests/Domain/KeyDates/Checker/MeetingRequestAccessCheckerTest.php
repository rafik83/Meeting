<?php

namespace Proximum\Vimeet\Tests\Domain\KeyDates\Checker;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingRequestAccessChecker;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class MeetingRequestAccessCheckerTest extends TestCase
{
    public function testAllowedToAccessTrueAsDateIsNull()
    {
        $date  = new \DateTime();
        $event = EventFactory::createEvent();

        $meetingRequestAccessChecker = new MeetingRequestAccessChecker($date);
        $this->assertEquals(true, $meetingRequestAccessChecker->allowedToAccess($event));
    }

    public function testAllowedToAccessTrueAsDateIsInTheFuture()
    {
        $date = new \DateTime('2016-09-12 10:10');
        $dateCloseMeetingRequest = new \DateTime('2016-10-12 10:10');
        $event = EventFactory::createEvent();
        $event->getConfiguration()->setDates(null, null, null, $dateCloseMeetingRequest);

        $meetingRequestAccessChecker = new MeetingRequestAccessChecker($date);
        $this->assertEquals(true, $meetingRequestAccessChecker->allowedToAccess($event));
    }

    public function testAllowedToAccessFalseAsDateIsPassed()
    {
        $date = new \DateTime('2016-10-14 10:10');
        $dateCloseMeetingRequest = new \DateTime('2016-10-12 10:10');
        $event = EventFactory::createEvent();
        $event->getConfiguration()->setDates(null, null, null, $dateCloseMeetingRequest);

        $meetingRequestAccessChecker = new MeetingRequestAccessChecker($date);
        $this->assertEquals(false, $meetingRequestAccessChecker->allowedToAccess($event));
    }
}
