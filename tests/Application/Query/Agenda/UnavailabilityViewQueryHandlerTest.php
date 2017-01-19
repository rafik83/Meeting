<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Agenda;

use Proximum\Vimeet\Application\Query\Agenda\UnavailabilityViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\UnavailabilityViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\UnavailabilityView;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class UnavailabilityViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $begin = new \DateTime('2016-10-12 12:00:00');
        $end   = new \DateTime('2016-10-12 14:00:00');
        $sheet       = SheetFactory::create($event);
        $participant = ParticipantFactory::create($sheet);

        $unavailability = new Unavailability($participant, $begin, $end);
        $reflection = new \ReflectionClass(Unavailability::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($unavailability, 1);
        $property->setAccessible(false);

        $handler = new UnavailabilityViewQueryHandler();
        $result = $handler->handle(new UnavailabilityViewQuery(
            $unavailability,
            $event
        ));

        // Expected
        $expected = new UnavailabilityView(1, $begin, $end, 'Europe/Paris');

        $this->assertEquals($expected, $result);
    }
}
