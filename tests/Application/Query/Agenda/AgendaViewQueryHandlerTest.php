<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Agenda;

use Proximum\Vimeet\Application\Query\Agenda\AgendaViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\AgendaViewQueryHandler;
use Proximum\Vimeet\Application\Query\Agenda\DayViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\DayViewQueryHandler;
use Proximum\Vimeet\Application\Query\Agenda\ParticipantViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\ParticipantViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\AgendaView;
use Proximum\Vimeet\Application\View\Agenda\DayView;
use Proximum\Vimeet\Application\View\Agenda\ParticipantView;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingPublishedAccessChecker;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Model\Unavailability\Category;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Event\DayRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;
use Proximum\Vimeet\Domain\User\Agenda\Phone\ValidationRequiredChecker;
use Proximum\Vimeet\Infrastructure\Repository\User\Event\ExtraDataRepository;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use PHPUnit\Framework\TestCase;

class AgendaViewQueryHandlerTest extends TestCase
{
    /** @var ValidationRequiredChecker */
    private $validationRequiredChecker;

    /** @var ExtraDataRepository */
    private $extraDataRepository;

    public function setUp()
    {
        $this->validationRequiredChecker = $this->prophesize(ValidationRequiredChecker::class);
        $this->extraDataRepository = $this->prophesize(ExtraDataRepository::class);
    }

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
        $happeningParticipation = new HappeningParticipation($happening, $user);

        $unavailability = new Unavailability($user, $event, $begin, $end);

        // Mock
        $dayRepository  = $this->prophesize(DayRepositoryInterface::class);
        $dayRepository->findByEvent($event)->shouldBeCalled()->willReturn([$day]);

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository->isUserParticipantMultipleSheetsInEvent($user, $event)->shouldBeCalled()->willReturn(true);

        $happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $happeningParticipationRepository->findByUser($user, $event, ['disabled' => false])->shouldBeCalled()->willReturn([
            $happeningParticipation
        ]);

        $this->validationRequiredChecker->handle($sheet, $user)->shouldBeCalled()->willReturn(true);
        $this->extraDataRepository
            ->getExtraDataForEventNameAndUser($event, Type::PHONE_CONFIRMATION_IGNORED, $user)
            ->shouldBeCalled()
            ->willReturn(new User\Event\ExtraData(
                $user,
                $event,
                Type::PHONE_CONFIRMATION_IGNORED,
                '',
                new \DateTime()
            ))
        ;

        $unavailabilityRepository = $this->prophesize(UnavailabilityRepositoryInterface::class);
        $unavailabilityRepository->findByUserAndEvent($user, $event)->shouldBeCalled()->willReturn([$unavailability]);

        $massUnavailabilityRepository     = $this->prophesize(MassRepositoryInterface::class);
        $massUnavailabilityRepository->findByEvent($event, 'fr')->shouldBeCalled()->willReturn([$mass]);
        $dayViewQueryHandler              = $this->prophesize(DayViewQueryHandler::class);
        $dayView = new DayView($begin, $end, $event->getConfiguration()->getScheduleScale(), [], [], [], [], []);
        $dayViewQueryHandler
            ->handle(new DayViewQuery($day, $sheet, $event, $participant, $user, true, 'fr', [$happeningParticipation], [$unavailability], [$mass]))
            ->shouldBeCalled()
            ->willReturn($dayView)
        ;

        $participantHandler = $this->prophesize(ParticipantViewQueryHandler::class);
        $participantHandler->handle(new ParticipantViewQuery([$participant], 'fr'))->shouldBeCalled()->willReturn([new ParticipantView(1, 'fullName')]);

        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository->findByUserAndEvent($user, $event)->shouldNotBeCalled();

        $meetingPublishedAccessChecker = $this->prophesize(MeetingPublishedAccessChecker::class);
        $meetingPublishedAccessChecker->allowedToAccess($event)->shouldBeCalled()->willReturn(false);

        // Reflection
        $reflection = new \ReflectionClass(User::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($user, 1);
        $property->setAccessible(false);

        // Handler
        $handler = new AgendaViewQueryHandler(
            $dayRepository->reveal(),
            $sheetRepository->reveal(),
            $dayViewQueryHandler->reveal(),
            $happeningParticipationRepository->reveal(),
            $unavailabilityRepository->reveal(),
            $massUnavailabilityRepository->reveal(),
            $participantHandler->reveal(),
            $meetingRepository->reveal(),
            $meetingPublishedAccessChecker->reveal(),
            $this->validationRequiredChecker->reveal(),
            $this->extraDataRepository->reveal()
        );
        $result = $handler->handle(new AgendaViewQuery($event, $sheet, $participant, 'fr', $user));

        // Expected
        $expected = new AgendaView([$dayView], $sheet, $participant, true, [new ParticipantView(1, 'fullName')], false);

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
        $happeningParticipation = new HappeningParticipation($happening, $user2);

        $unavailability = new Unavailability($user2, $event, $begin, $end);

        // Mock
        $dayRepository = $this->prophesize(DayRepositoryInterface::class);
        $dayRepository->findByEvent($event)->shouldBeCalled()->willReturn([$day]);

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository->isUserParticipantMultipleSheetsInEvent($user2, $event)->shouldBeCalled()->willReturn(true);

        $happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $happeningParticipationRepository
            ->findByUser($user2, $event, ['disabled' => false])
            ->shouldBeCalled()
            ->willReturn([
                $happeningParticipation
            ])
        ;

        $unavailabilityRepository = $this->prophesize(UnavailabilityRepositoryInterface::class);
        $unavailabilityRepository->findByUserAndEvent($user2, $event)->shouldBeCalled()->willReturn([$unavailability]);

        $massUnavailabilityRepository     = $this->prophesize(MassRepositoryInterface::class);
        $massUnavailabilityRepository->findByEvent($event, 'fr')->shouldBeCalled()->willReturn([$mass]);
        $dayViewQueryHandler              = $this->prophesize(DayViewQueryHandler::class);
        $dayView = new DayView($begin, $end, $event->getConfiguration()->getScheduleScale(), [], [], [], [], []);
        $dayViewQueryHandler
            ->handle(new DayViewQuery($day, $sheet, $event, $participant2, $user, true, 'fr', [$happeningParticipation], [$unavailability], [$mass], []))
            ->shouldBeCalled()
            ->willReturn($dayView)
        ;

        $participantHandler = $this->prophesize(ParticipantViewQueryHandler::class);
        $participantHandler->handle(new ParticipantViewQuery([$participant, $participant2], 'fr'))->shouldBeCalled()->willReturn([new ParticipantView(1, 'fullName'), new ParticipantView(2, 'fullName2')]);

        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository->findByUserAndEvent($user2, $event)->shouldBeCalled()->willReturn([]);

        $meetingPublishedAccessChecker = $this->prophesize(MeetingPublishedAccessChecker::class);
        $meetingPublishedAccessChecker->allowedToAccess($event)->shouldBeCalled()->willReturn(true);

        // Reflection
        $reflection = new \ReflectionClass(User::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($user, 1);
        $property->setValue($user2, 2);
        $property->setAccessible(false);

        $this->validationRequiredChecker->handle($sheet, $user)->shouldBeCalled()->willReturn(true);
        $this->extraDataRepository
            ->getExtraDataForEventNameAndUser($event, Type::PHONE_CONFIRMATION_IGNORED, $user)
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        // Handler
        $handler = new AgendaViewQueryHandler(
            $dayRepository->reveal(),
            $sheetRepository->reveal(),
            $dayViewQueryHandler->reveal(),
            $happeningParticipationRepository->reveal(),
            $unavailabilityRepository->reveal(),
            $massUnavailabilityRepository->reveal(),
            $participantHandler->reveal(),
            $meetingRepository->reveal(),
            $meetingPublishedAccessChecker->reveal(),
            $this->validationRequiredChecker->reveal(),
            $this->extraDataRepository->reveal()
        );
        $result = $handler->handle(new AgendaViewQuery($event, $sheet, $participant2, 'fr', $user));

        // Expected
        $expected = new AgendaView([$dayView], $sheet, $participant2, false, [new ParticipantView(1, 'fullName'), new ParticipantView(2, 'fullName2')], true);

        $this->assertEquals($expected, $result);
    }
}
