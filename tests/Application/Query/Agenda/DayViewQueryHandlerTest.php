<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Agenda;

use Proximum\Vimeet\Application\Query\Agenda\DayViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\DayViewQueryHandler;
use Proximum\Vimeet\Application\Query\Agenda\HappeningViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\HappeningViewQueryHandler;
use Proximum\Vimeet\Application\Query\Agenda\MassUnavailabilityViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\MassUnavailabilityViewQueryHandler;
use Proximum\Vimeet\Application\Query\Agenda\UnavailabilityViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\UnavailabilityViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\DayView;
use Proximum\Vimeet\Application\View\Agenda\HappeningView;
use Proximum\Vimeet\Application\View\Agenda\MassUnavailabilityView;
use Proximum\Vimeet\Application\View\Agenda\UnavailabilityView;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class DayViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event       = EventFactory::createEvent();
        $category    = null;
        $startTime   = new \DateTime('2016-10-12 10:00:00');
        $endTime     = new \DateTime('2016-10-12 18:00:00');
        $eventDay    = new Day($event, $startTime, $endTime);
        $sheet       = SheetFactory::create($event);
        $participant = ParticipantFactory::create($sheet);
        $massCategory = new Unavailability\Category($event, 'picto', 'title', 'leftColor', 'rightColor');

        // Data
        $beginHappening1 = new \DateTime('2016-10-12 12:00:00');
        $beginHappening2 = new \DateTime('2016-10-12 15:30:00');
        $endHappening1   = new \DateTime('2016-10-12 14:00:00');
        $endHappening2   = new \DateTime('2016-10-12 16:50:00');
        $categoryH1      = new Happening\Category($event, 'Conference', 1, '#123123', '#123123');
        $categoryH2      = new Happening\Category($event, 'RDV', 2, '#123123', '#123123');
        $happening1 = new Happening(
            $event,
            $beginHappening1,
            $endHappening1,
            $categoryH1
        );

        $happening2 = new Happening(
            $event,
            $beginHappening2,
            $endHappening2,
            $categoryH2
        );

        $participation1 = new HappeningParticipation($happening1, $participant);
        $participation2 = new HappeningParticipation($happening2, $participant);

        $reflection = new \ReflectionClass(Happening::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($happening1, 1);
        $property->setValue($happening2, 2);
        $property->setAccessible(false);

        $unavailability = new Unavailability($participant, $beginHappening2, $endHappening2);
        $mass = new Unavailability\Mass($event, $massCategory, 'name', $beginHappening1, $endHappening1, true);

        // Expected
        $happeningView1 = new HappeningView(
            1,
            $beginHappening1,
            $endHappening1,
            'title',
            'description',
            [],
            'picto',
            'leftColor',
            'rightColor'
        );
        $happeningView2 = new HappeningView(
            2,
            $beginHappening2,
            $endHappening2,
            'title2',
            'description2',
            [],
            'picto',
            'leftColor',
            'rightColor'
        );

        $massView = new MassUnavailabilityView(1, $beginHappening1, $endHappening1, 'title', 'description', 'picto', 'leftColor', 'rightColor');
        $unavailabilityView = new UnavailabilityView(1, $beginHappening2, $endHappening2);

        $expected = new DayView(
            $startTime,
            $endTime,
            $event->getConfiguration()->getScheduleScale(),
            [$happeningView1, $happeningView2],
            [$unavailabilityView],
            [$massView]
        );

        // Mock
        $happeningViewQueryHandler = $this->prophesize(HappeningViewQueryHandler::class);
        $happeningViewQueryHandler->handle(
            new HappeningViewQuery(
                $happening1,
                'fr'
            )
        )->shouldBeCalled()->willReturn($happeningView1);
        $happeningViewQueryHandler->handle(
            new HappeningViewQuery(
                $happening2,
                'fr'
            )
        )->shouldBeCalled()->willReturn($happeningView2);

        $massHandler           = $this->prophesize(MassUnavailabilityViewQueryHandler::class);
        $unavailabilityHandler = $this->prophesize(UnavailabilityViewQueryHandler::class);
        $massHandler->handle(new MassUnavailabilityViewQuery($mass, 'fr'))->shouldBeCalled()->willReturn($massView);
        $unavailabilityHandler->handle(new UnavailabilityViewQuery($unavailability))->shouldBeCalled()->willReturn($unavailabilityView);


        $handler = new DayViewQueryHandler(
            $happeningViewQueryHandler->reveal(),
            $unavailabilityHandler->reveal(),
            $massHandler->reveal()
        );
        $result = $handler->handle(new DayViewQuery(
            $eventDay,
            'fr',
            [$participation1, $participation2],
            [$unavailability],
            [$mass]
        ));

        $this->assertEquals($expected, $result);
    }
}
