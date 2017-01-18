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
use Proximum\Vimeet\Application\Query\Agenda\ParticipantViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\ParticipantViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\AgendaView;
use Proximum\Vimeet\Application\View\Agenda\DayView;
use Proximum\Vimeet\Application\View\Agenda\ParticipantView;
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

        $participantHandler = $this->prophesize(ParticipantViewQueryHandler::class);
        $participantHandler->handle(new ParticipantViewQuery([$participant], 'fr'))->shouldBeCalled()->willReturn([new ParticipantView(1, 'fullName')]);

        // Reflection
        $reflection = new \ReflectionClass(User::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($user, 1);
        $property->setAccessible(false);

        // Handler
        $handler = new AgendaViewQueryHandler(
            $dayRepository->reveal(),
            $dayViewQueryHandler->reveal(),
            $happeningParticipationRepository->reveal(),
            $unavailabilityRepository->reveal(),
            $massUnavailabilityRepository->reveal(),
            $participantHandler->reveal()
        );
        $result = $handler->handle(new AgendaViewQuery($event, $sheet, $participant, $user, 'fr'));

        // Expected
        $expected = new AgendaView([$dayView], $sheet, $participant, true, [new ParticipantView(1, 'fullName')]);

        $this->assertEquals($expected, $result);
    }

    public function testHandleMultipleParticipant()
    {
        $event        = EventFactory::createEvent();
        $user         = new User('user@vimeet.com', 'salt', 'password', 'fr');
        $user2        = new User('user@vimeet.com2', 'salt2', 'password2', 'fr');
        $sheet        = SheetFactory::create($event, $user);
        $participant  = ParticipantFactory::create($sheet, $user);
        $participant2 = ParticipantFactory::create($sheet, $user2);

        $begin = new \DateTime('2016-10-12 10:00:00');
        $end   = new \DateTime('2016-10-12 18:00:00');
        $day   = new Day($event, $begin, $end);

        $category = new Category($event, 'picto', 'title', 'leftColor', 'rightColor');
        $mass     = new Mass($event, $category, 'name', $begin, $end, true);

        $categoryH = new Happening\Category($event, 'picto', 1, 'leftColor', 'rightColor');
        $happening = new Happening($event, $begin, $end, $categoryH, false, 100);
        $happeningParticipation = new HappeningParticipation($happening, $participant2);

        $unavailability = new Unavailability($participant2, $begin, $end);

        // Mock
        $dayRepository  = $this->prophesize(DayRepositoryInterface::class);
        $dayRepository->findByEvent($event)->shouldBeCalled()->willReturn([$day]);

        $happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $happeningParticipationRepository->findByParticipant($participant2)->shouldBeCalled()->willReturn([
            $happeningParticipation
        ]);

        $unavailabilityRepository = $this->prophesize(UnavailabilityRepositoryInterface::class);
        $unavailabilityRepository->findByParticipant($participant2)->shouldBeCalled()->willReturn([$unavailability]);

        $massUnavailabilityRepository     = $this->prophesize(MassRepositoryInterface::class);
        $massUnavailabilityRepository->findByEvent($event, 'fr')->shouldBeCalled()->willReturn([$mass]);
        $dayViewQueryHandler              = $this->prophesize(DayViewQueryHandler::class);
        $dayView = new DayView($begin, $end, $event->getConfiguration()->getScheduleScale(), [], [], []);
        $dayViewQueryHandler
            ->handle(new DayViewQuery($day, 'fr', [$happeningParticipation], [$unavailability], [$mass]))
            ->shouldBeCalled()
            ->willReturn($dayView)
        ;

        $participantHandler = $this->prophesize(ParticipantViewQueryHandler::class);
        $participantHandler->handle(new ParticipantViewQuery([$participant, $participant2], 'fr'))->shouldBeCalled()->willReturn([new ParticipantView(1, 'fullName'), new ParticipantView(2, 'fullName2')]);

        // Reflection
        $reflection = new \ReflectionClass(User::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($user, 1);
        $property->setValue($user2, 2);
        $property->setAccessible(false);

        // Handler
        $handler = new AgendaViewQueryHandler(
            $dayRepository->reveal(),
            $dayViewQueryHandler->reveal(),
            $happeningParticipationRepository->reveal(),
            $unavailabilityRepository->reveal(),
            $massUnavailabilityRepository->reveal(),
            $participantHandler->reveal()
        );
        $result = $handler->handle(new AgendaViewQuery($event, $sheet, $participant2, $user2, 'fr'));

        // Expected
        $expected = new AgendaView([$dayView], $sheet, $participant2, false, [new ParticipantView(1, 'fullName'), new ParticipantView(2, 'fullName2')]);

        $this->assertEquals($expected, $result);
    }
}
