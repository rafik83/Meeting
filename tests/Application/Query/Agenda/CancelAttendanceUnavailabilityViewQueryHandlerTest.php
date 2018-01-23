<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Agenda;

use Proximum\Vimeet\Application\Query\Agenda\CancelAttendanceUnavailabilityViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\CancelAttendanceUnavailabilityViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\CancelAttendanceUnavailabilityView;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use PHPUnit\Framework\TestCase;

class CancelAttendanceUnavailabilityViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $begin = new \DateTime('2016-10-12 09:00:00');
        $end   = new \DateTime('2016-10-12 18:00:00');
        $day   = new Day($event, $begin, $end);

        $handler = new CancelAttendanceUnavailabilityViewQueryHandler();
        $result = $handler->handle(new CancelAttendanceUnavailabilityViewQuery(
            $event,
            $day
        ));

        // Expected
        $expected = new CancelAttendanceUnavailabilityView($begin, $end, 'Europe/Paris');

        $this->assertEquals($expected, $result);
    }
}
