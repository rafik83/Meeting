<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\KeyDates\Checker;

use Proximum\Vimeet\Domain\KeyDates\Checker\CloseAnsweringMeetingRequestAccessChecker;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class CloseAnsweringMeetingRequestAccessCheckerTest extends \PHPUnit_Framework_TestCase
{
    public function testAllowedToAccessTrueAsDateIsNull()
    {
        $date  = new \DateTime();
        $event = EventFactory::createEvent();

        $closeAnsweringMeetingRequestAccessChecker = new CloseAnsweringMeetingRequestAccessChecker($date);
        $this->assertEquals(true, $closeAnsweringMeetingRequestAccessChecker->allowedToAccess($event));
    }

    public function testAllowedToAccessTrueAsDateIsInTheFuture()
    {
        $date = new \DateTime('2016-09-12 10:10');
        $dateCloseAnsweringMeetingRequest = new \DateTime('2016-10-12 10:10');
        $event = EventFactory::createEvent();
        $event->getConfiguration()->setDates(null, null, null, null, $dateCloseAnsweringMeetingRequest);

        $closeAnsweringMeetingRequestAccessChecker = new CloseAnsweringMeetingRequestAccessChecker($date);
        $this->assertEquals(true, $closeAnsweringMeetingRequestAccessChecker->allowedToAccess($event));
    }

    public function testAllowedToAccessFalseAsDateIsPassed()
    {
        $date = new \DateTime('2016-10-14 10:10');
        $dateCloseAnsweringMeetingRequest = new \DateTime('2016-10-12 10:10');
        $event = EventFactory::createEvent();
        $event->getConfiguration()->setDates(null, null, null, null, $dateCloseAnsweringMeetingRequest);

        $closeAnsweringMeetingRequestAccessChecker = new CloseAnsweringMeetingRequestAccessChecker($date);
        $this->assertEquals(false, $closeAnsweringMeetingRequestAccessChecker->allowedToAccess($event));
    }
}
