<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\KeyDates\Checker;

use Proximum\Vimeet\Domain\KeyDates\Checker\CloseMeetingRequestAccessChecker;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class CloseMeetingRequestAccessCheckerTest extends \PHPUnit_Framework_TestCase
{
    public function testAllowedToAccessTrueAsDateIsNull()
    {
        $date  = new \DateTime();
        $event = EventFactory::createEvent();

        $closeMeetingRequestAccessChecker = new CloseMeetingRequestAccessChecker($date);
        $this->assertEquals(true, $closeMeetingRequestAccessChecker->allowedToAccess($event));
    }

    public function testAllowedToAccessTrueAsDateIsInTheFuture()
    {
        $date = new \DateTime('2016-09-12 10:10');
        $dateCloseMeetingRequest = new \DateTime('2016-10-12 10:10');
        $event = EventFactory::createEvent();
        $event->getConfiguration()->setDates(null, null, null, $dateCloseMeetingRequest);

        $closeMeetingRequestAccessChecker = new CloseMeetingRequestAccessChecker($date);
        $this->assertEquals(true, $closeMeetingRequestAccessChecker->allowedToAccess($event));
    }

    public function testAllowedToAccessFalseAsDateIsPassed()
    {
        $date = new \DateTime('2016-10-14 10:10');
        $dateCloseMeetingRequest = new \DateTime('2016-10-12 10:10');
        $event = EventFactory::createEvent();
        $event->getConfiguration()->setDates(null, null, null, $dateCloseMeetingRequest);

        $closeMeetingRequestAccessChecker = new CloseMeetingRequestAccessChecker($date);
        $this->assertEquals(false, $closeMeetingRequestAccessChecker->allowedToAccess($event));
    }
}
