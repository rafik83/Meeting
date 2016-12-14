<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Agenda;

use Proximum\Vimeet\Application\Components\Sheet\SheetGuesser;
use Proximum\Vimeet\Application\Query\Agenda\AgendaViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\AgendaViewQueryHandler;
use Proximum\Vimeet\Application\Query\Agenda\DayViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\DayViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\AgendaView;
use Proximum\Vimeet\Application\View\Agenda\DayView;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Model\Unavailability\Category;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Event\DayRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class AgendaViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event       = EventFactory::createEvent();
        $user        = new User('user@vimeet.com', 'salt', 'password', 'fr');
        $sheet       = SheetFactory::create($event, $user);
        $participant = ParticipantFactory::create($sheet, $user);

        $begin = new \DateTime('2016-10-12 10:00:00');
        $end   = new \DateTime('2016-10-12 18:00:00');
        $day   = new Day($event, $begin, $end);

        $category = new Category($event, 'picto', 'title', 'leftColor', 'rightColor');
        $mass     = new Mass($event, $category, 'name', $begin, $end, true);

        $categoryH = new Happening\Category($event, 'picto', 1, 'leftColor', 'rightColor');
        $happening = new Happening($event, $begin, $end, $categoryH, false, 100);
        $happeningParticipation = new HappeningParticipation($happening, $participant);

        $unavailability = new Unavailability($participant, $begin, $end);

        // Mock
        $sheetGuesser = $this->prophesize(SheetGuesser::class);
        $sheetGuesser->getUserSheet($user, $event, 'fr')->shouldBeCalled()->willReturn($sheet);

        $dayRepository  = $this->prophesize(DayRepositoryInterface::class);
        $dayRepository->findByEvent($event)->shouldBeCalled()->willReturn([$day]);


        $happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $happeningParticipationRepository->findByParticipant($participant)->shouldBeCalled()->willReturn([
            $happeningParticipation
        ]);

        $unavailabilityRepository = $this->prophesize(UnavailabilityRepositoryInterface::class);
        $unavailabilityRepository->findByParticipant($participant)->shouldBeCalled()->willReturn([$unavailability]);

        $massUnavailabilityRepository     = $this->prophesize(MassRepositoryInterface::class);
        $massUnavailabilityRepository->findByEvent($event, 'fr')->shouldBeCalled()->willReturn([$mass]);
        $dayViewQueryHandler              = $this->prophesize(DayViewQueryHandler::class);
        $dayView = new DayView($begin, $end, $event->getConfiguration()->getScheduleScale(), [], [], []);
        $dayViewQueryHandler
            ->handle(new DayViewQuery($day, 'fr', [$happeningParticipation], [$unavailability], [$mass]))
            ->shouldBeCalled()
            ->willReturn($dayView)
        ;

        // Handler
        $handler = new AgendaViewQueryHandler(
            $sheetGuesser->reveal(),
            $dayRepository->reveal(),
            $dayViewQueryHandler->reveal(),
            $happeningParticipationRepository->reveal(),
            $unavailabilityRepository->reveal(),
            $massUnavailabilityRepository->reveal()
        );
        $result = $handler->handle(new AgendaViewQuery($event, $user, 'fr'));


        // Expected
        $expected = new AgendaView([$dayView], $sheet);

        $this->assertEquals($expected, $result);
    }
}
