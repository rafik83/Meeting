<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Happening;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Exception\Happening\MissingEventDayConfigurationException;
use Proximum\Vimeet\Application\Query\Happening\DayViewQuery;
use Proximum\Vimeet\Application\Query\Happening\DayViewQueryHandler;
use Proximum\Vimeet\Application\Query\Happening\FullHappeningQuery;
use Proximum\Vimeet\Application\Query\Happening\FullHappeningQueryHandler;
use Proximum\Vimeet\Application\Query\Happening\HappeningParticipationQueryHandler;
use Proximum\Vimeet\Application\Query\Happening\ProgramViewQuery;
use Proximum\Vimeet\Application\Query\Happening\ProgramViewQueryHandler;
use Proximum\Vimeet\Application\View\Happening\DayView;
use Proximum\Vimeet\Application\View\Happening\ProgramView;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Event\DayRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class ProgramViewQueryHandlerTest extends TestCase
{
    public function testHandleException()
    {
        $this->expectException(MissingEventDayConfigurationException::class);

        $event = EventFactory::createEvent();
        $user  = new User('user@vimeet.com', 'salt', 'password', 'fr');
        $sheet = SheetFactory::create($event, $user);

        // Mock
        $dayRepository = $this->prophesize(DayRepositoryInterface::class);
        $dayRepository->findByEvent($event)->shouldBeCalled()->willReturn([]);
        $dayViewQueryHandler = $this->prophesize(DayViewQueryHandler::class);
        $happeningParticipationQueryHandler = $this->prophesize(HappeningParticipationQueryHandler::class);
        $massRepository = $this->prophesize(MassRepositoryInterface::class);
        $fullHappeningQueryHandler = $this->prophesize(FullHappeningQueryHandler::class);

        // Handler
        $handler = new ProgramViewQueryHandler(
            $dayRepository->reveal(),
            $dayViewQueryHandler->reveal(),
            $happeningParticipationQueryHandler->reveal(),
            $massRepository->reveal(),
            $fullHappeningQueryHandler->reveal()
        );

        $handler->handle(
            new ProgramViewQuery(
                $event,
                $sheet,
                $user,
                'fr',
                null,
                null
            )
        );
    }

    public function testHandle()
    {
        $event      = EventFactory::createEvent();
        $user       = new User('user@vimeet.com', 'salt', 'password', 'fr');
        $sheet      = SheetFactory::create($event, $user);
        $startTime1 = new \DateTime('2016-10-12 10:00:00');
        $endTime1   = new \DateTime('2016-10-12 18:00:00');
        $startTime2 = new \DateTime('2016-10-13 10:00:00');
        $endTime2   = new \DateTime('2016-10-13 18:00:00');
        $eventDay1  = new Day($event, $startTime1, $endTime1);
        $eventDay2  = new Day($event, $startTime2, $endTime2);

        // Expected
        $dayView1 = new DayView($startTime1, $endTime1, $event->getConfiguration()->getScheduleScale(), []);
        $dayView2 = new DayView($startTime2, $endTime2, $event->getConfiguration()->getScheduleScale(), []);

        $expected = new ProgramView([
            $dayView1, $dayView2,
        ], null, null);

        // Mock
        $dayRepository = $this->prophesize(DayRepositoryInterface::class);
        $dayRepository->findByEvent($event)->shouldBeCalled()->willReturn([
            $eventDay1,
            $eventDay2,
        ]);

        $dayViewQueryHandler = $this->prophesize(DayViewQueryHandler::class);
        $dayViewQueryHandler->handle(new DayViewQuery(
            $event,
            $sheet,
            $user,
            $eventDay1,
             'fr',
            null,
            []
        ))->shouldBeCalled()->willReturn($dayView1);
        $dayViewQueryHandler->handle(new DayViewQuery(
            $event,
            $sheet,
            $user,
            $eventDay2,
            'fr',
            null,
            []
        ))->shouldBeCalled()->willReturn($dayView2);

        $happeningParticipationQueryHandler = $this->prophesize(HappeningParticipationQueryHandler::class);
        $happeningParticipationQueryHandler->handle($expected, $sheet, $user);

        $massRepository = $this->prophesize(MassRepositoryInterface::class);
        $massRepository->findByType($sheet->getType(), 'fr')->shouldBeCalled()->willReturn([]);

        $fullHappeningQueryHandler = $this->prophesize(FullHappeningQueryHandler::class);
        $fullHappeningQueryHandler->handle(new FullHappeningQuery($expected, $event))->shouldBeCalled();

        // Handler
        $handler = new ProgramViewQueryHandler(
            $dayRepository->reveal(),
            $dayViewQueryHandler->reveal(),
            $happeningParticipationQueryHandler->reveal(),
            $massRepository->reveal(),
            $fullHappeningQueryHandler->reveal()
        );

        $result = $handler->handle(
            new ProgramViewQuery(
                $event,
                $sheet,
                $user,
                'fr',
                null,
                null
            )
        );

        $this->assertEquals($expected, $result);
    }
}
