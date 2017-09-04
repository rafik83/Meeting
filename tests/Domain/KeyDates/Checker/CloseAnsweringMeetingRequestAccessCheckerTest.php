<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\KeyDates\Checker;

use Proximum\Vimeet\Domain\KeyDates\Checker\AnsweringMeetingRequestAccessChecker;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use PHPUnit\Framework\TestCase;

class AnsweringMeetingRequestAccessCheckerTest extends TestCase
{
    public function testAllowedToAccessTrueAsDateIsNull()
    {
        $date  = new \DateTime();
        $event = EventFactory::createEvent();

        $answeringMeetingRequestAccessChecker = new AnsweringMeetingRequestAccessChecker($date);
        $this->assertEquals(true, $answeringMeetingRequestAccessChecker->allowedToAccess($event));
    }

    public function testAllowedToAccessTrueAsDateIsInTheFuture()
    {
        $date = new \DateTime('2016-09-12 10:10');
        $dateCloseAnsweringMeetingRequest = new \DateTime('2016-10-12 10:10');
        $event = EventFactory::createEvent();
        $event->getConfiguration()->setDates(null, null, null, null, $dateCloseAnsweringMeetingRequest);

        $answeringMeetingRequestAccessChecker = new AnsweringMeetingRequestAccessChecker($date);
        $this->assertEquals(true, $answeringMeetingRequestAccessChecker->allowedToAccess($event));
    }

    public function testAllowedToAccessFalseAsDateIsPassed()
    {
        $date = new \DateTime('2016-10-14 10:10');
        $dateCloseAnsweringMeetingRequest = new \DateTime('2016-10-12 10:10');
        $event = EventFactory::createEvent();
        $event->getConfiguration()->setDates(null, null, null, null, $dateCloseAnsweringMeetingRequest);

        $answeringMeetingRequestAccessChecker = new AnsweringMeetingRequestAccessChecker($date);
        $this->assertEquals(false, $answeringMeetingRequestAccessChecker->allowedToAccess($event));
    }
}
