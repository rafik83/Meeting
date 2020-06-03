<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Unavailability;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\Unavailability\Create;
use Proximum\Vimeet\Application\Command\Unavailability\CreateHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Unavailability\AddUnavailabilityEvent;
use Proximum\Vimeet\Application\Exception\Unavailability\CanNotCreateUnavailabilityException;
use Proximum\Vimeet\Application\Exception\Unavailability\NoParticipantSelectedException;
use Proximum\Vimeet\Application\Exception\Unavailability\ParticipantsSelectedWithMeetingOrHappeningException;
use Proximum\Vimeet\Application\Exception\Unavailability\ParticipantsWithUnavailabilityException;
use Proximum\Vimeet\Application\Exception\Unavailability\TimeOutOfRangeException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class CreateHandlerTest extends TestCase
{
    /** @var UnavailabilityRepositoryInterface|ObjectProphecy */
    private $unavailabilityRepository;

    /** @var ParticipantRepositoryInterface|ObjectProphecy */
    private $participantRepository;

    /** @var ParticipantInfoGuesser|ObjectProphecy */
    private $paticipantInfoGuesser;

    /** @var DelayedEventDispatcher|ObjectProphecy */
    private $eventDispatcher;

    public function setUp(): void
    {
        $this->unavailabilityRepository = $this->prophesize(UnavailabilityRepositoryInterface::class);
        $this->participantRepository    = $this->prophesize(ParticipantRepositoryInterface::class);
        $this->paticipantInfoGuesser    = $this->prophesize(ParticipantInfoGuesser::class);
        $this->eventDispatcher          = $this->prophesize(DelayedEventDispatcher::class);
    }

    public function testCheckTimeOutOfDayFunctionWithBegin(): void
    {
        $this->expectException(TimeOutOfRangeException::class);

        $startTime = new \DateTime('2016-10-12 10:00:00.000');
        $endTime   = new \DateTime('2016-10-12 18:00:00.000');

        $event = EventFactory::createEvent();
        $day   = new Day($event, $startTime, $endTime);
        $event->setDays([$day]);
        $user   = UserFactory::create();
        $sheet  = SheetFactory::create($event, $user);
        $create = new Create($event, $sheet, $user, 'fr', 'Europe/Paris');

        $handler = new CreateHandler(
            $this->unavailabilityRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->paticipantInfoGuesser->reveal(),
            $this->eventDispatcher->reveal()
        );

        $begin      = new \DateTime('2016-10-12 08:00:00.000');
        $end        = new \DateTime('2016-10-12 09:00:00.000');
        $reflection = new \ReflectionClass(CreateHandler::class);
        $method     = $reflection->getMethod('checkTimeOutOfDay');
        $method->setAccessible(true);
        $method->invokeArgs($handler, [$create, $begin, $end]);
    }

    public function testCheckTimeOutOfDayFunctionWithEnd(): void
    {
        $this->expectException(TimeOutOfRangeException::class);

        $startTime = new \DateTime('2016-10-12 10:00:00.000');
        $endTime   = new \DateTime('2016-10-12 18:00:00.000');

        $event = EventFactory::createEvent();
        $day   = new Day($event, $startTime, $endTime);
        $event->setDays([$day]);
        $user   = UserFactory::create();
        $sheet  = SheetFactory::create($event, $user);
        $create = new Create($event, $sheet, $user, 'fr', 'Europe/Paris');

        $handler = new CreateHandler(
            $this->unavailabilityRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->paticipantInfoGuesser->reveal(),
            $this->eventDispatcher->reveal()
        );

        $begin      = new \DateTime('2016-10-12 19:00:00.000');
        $end        = new \DateTime('2016-10-12 20:00:00.000');
        $reflection = new \ReflectionClass(CreateHandler::class);
        $method     = $reflection->getMethod('checkTimeOutOfDay');
        $method->setAccessible(true);
        $method->invokeArgs($handler, [$create, $begin, $end]);
    }

    public function testCheckTimeOutOfDayFunctionWithoutException(): void
    {
        $startTime = new \DateTime('2016-10-12 10:00:00.000');
        $endTime   = new \DateTime('2016-10-12 18:00:00.000');

        $event = EventFactory::createEvent();
        $day   = new Day($event, $startTime, $endTime);
        $event->setDays([$day]);
        $user   = UserFactory::create();
        $sheet  = SheetFactory::create($event, $user);
        $create = new Create($event, $sheet, $user, 'fr', 'Europe/Paris');

        $handler = new CreateHandler(
            $this->unavailabilityRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->paticipantInfoGuesser->reveal(),
            $this->eventDispatcher->reveal()
        );

        $begin      = new \DateTime('2016-10-12 12:00:00.000');
        $end        = new \DateTime('2016-10-12 14:00:00.000');
        $reflection = new \ReflectionClass(CreateHandler::class);
        $method     = $reflection->getMethod('checkTimeOutOfDay');
        $method->setAccessible(true);
        $method->invokeArgs($handler, [$create, $begin, $end]);

        $this->assertTrue(true, 'Must not throw an exception');
    }

    public function testCheckParticipantsConflict(): void
    {
        $this->expectException(ParticipantsSelectedWithMeetingOrHappeningException::class);

        $startTime = new \DateTime('2016-10-12 10:00:00.000');
        $endTime   = new \DateTime('2016-10-12 18:00:00.000');
        $event     = EventFactory::createEvent();
        $day       = new Day($event, $startTime, $endTime);
        $event->setDays([$day]);
        $user                 = UserFactory::create();
        $sheet                = SheetFactory::create($event, $user);
        $participant          = ParticipantFactory::create($sheet, $user);
        $create               = new Create($event, $sheet, $user, 'fr', 'Europe/Paris');
        $create->participants = [$participant];
        $begin                = new \DateTime('2016-10-12 12:00:00.000');
        $end                  = new \DateTime('2016-10-12 14:00:00.000');

        $this->participantRepository
            ->getParticipantsWithoutMeetingAndHappening([$participant], $begin, $end)
            ->shouldBeCalled()
            ->willReturn([]);

        $handler = new CreateHandler(
            $this->unavailabilityRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->paticipantInfoGuesser->reveal(),
            $this->eventDispatcher->reveal()
        );

        $reflection = new \ReflectionClass(CreateHandler::class);
        $method     = $reflection->getMethod('checkParticipantsConflict');
        $method->setAccessible(true);
        $method->invokeArgs($handler, [$create, $begin, $end, 'fr']);
    }

    public function testCheckParticipantsConflictWithoutConflict(): void
    {
        $startTime = new \DateTime('2016-10-12 10:00:00.000');
        $endTime   = new \DateTime('2016-10-12 18:00:00.000');
        $event     = EventFactory::createEvent();
        $day       = new Day($event, $startTime, $endTime);
        $event->setDays([$day]);
        $user                 = UserFactory::create();
        $sheet                = SheetFactory::create($event, $user);
        $participant          = ParticipantFactory::create($sheet, $user);
        $create               = new Create($event, $sheet, $user, 'fr', 'Europe/Paris');
        $create->participants = [$participant];
        $begin                = new \DateTime('2016-10-12 12:00:00.000');
        $end                  = new \DateTime('2016-10-12 14:00:00.000');

        $this->participantRepository
            ->getParticipantsWithoutMeetingAndHappening([$participant], $begin, $end)
            ->shouldBeCalled()
            ->willReturn([$participant]);

        $handler = new CreateHandler(
            $this->unavailabilityRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->paticipantInfoGuesser->reveal(),
            $this->eventDispatcher->reveal()
        );

        $reflection = new \ReflectionClass(CreateHandler::class);
        $method     = $reflection->getMethod('checkParticipantsConflict');
        $method->setAccessible(true);
        $method->invokeArgs($handler, [$create, $begin, $end, 'fr']);

        $this->assertTrue(true, 'Must not throw an exception');
    }

    public function testTruncateOvertimeWithBeginOvertime(): void
    {
        $startTime = new \DateTime('2016-10-12 10:00:00.000');
        $endTime   = new \DateTime('2016-10-12 18:00:00.000');
        $event     = EventFactory::createEvent();
        $day       = new Day($event, $startTime, $endTime);
        $event->setDays([$day]);
        $user     = UserFactory::create();
        $sheet    = SheetFactory::create($event, $user);
        $create   = new Create($event, $sheet, $user, 'fr', 'Europe/Paris');
        $begin    = new \DateTime('2016-10-12 09:00:00.000');
        $end      = new \DateTime('2016-10-12 14:00:00.000');
        $endCheck = new \DateTime('2016-10-12 14:00:00.000');

        $handler = new CreateHandler(
            $this->unavailabilityRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->paticipantInfoGuesser->reveal(),
            $this->eventDispatcher->reveal()
        );

        $reflection = new \ReflectionClass(CreateHandler::class);
        $method     = $reflection->getMethod('truncateOvertime');
        $method->setAccessible(true);
        $method->invokeArgs($handler, [$create, &$begin, &$end]);

        $this->assertEquals($startTime, $begin);
        $this->assertEquals($endCheck, $end);
    }

    public function testTruncateOvertimeWithEndAndBeginOvertime(): void
    {
        $startTime = new \DateTime('2016-10-12 10:00:00.000');
        $endTime   = new \DateTime('2016-10-12 18:00:00.000');
        $event     = EventFactory::createEvent();
        $day       = new Day($event, $startTime, $endTime);
        $event->setDays([$day]);
        $user   = UserFactory::create();
        $sheet  = SheetFactory::create($event, $user);
        $create = new Create($event, $sheet, $user, 'fr', 'Europe/Paris');
        $begin  = new \DateTime('2016-10-12 09:00:00.000');
        $end    = new \DateTime('2016-10-12 19:00:00.000');

        $handler = new CreateHandler(
            $this->unavailabilityRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->paticipantInfoGuesser->reveal(),
            $this->eventDispatcher->reveal()
        );

        $reflection = new \ReflectionClass(CreateHandler::class);
        $method     = $reflection->getMethod('truncateOvertime');
        $method->setAccessible(true);
        $method->invokeArgs($handler, [$create, &$begin, &$end]);

        $this->assertEquals($startTime, $begin);
        $this->assertEquals($endTime, $end);
    }

    public function testTruncateOvertimeWithNoOvertime(): void
    {
        $startTime = new \DateTime('2016-10-12 10:00:00.000');
        $endTime   = new \DateTime('2016-10-12 18:00:00.000');
        $event     = EventFactory::createEvent();
        $day       = new Day($event, $startTime, $endTime);
        $event->setDays([$day]);
        $user       = UserFactory::create();
        $sheet      = SheetFactory::create($event, $user);
        $create     = new Create($event, $sheet, $user, 'fr', 'Europe/Paris');
        $begin      = new \DateTime('2016-10-12 11:00:00.000');
        $beginCheck = new \DateTime('2016-10-12 11:00:00.000');
        $end        = new \DateTime('2016-10-12 13:00:00.000');
        $endCheck   = new \DateTime('2016-10-12 13:00:00.000');

        $handler = new CreateHandler(
            $this->unavailabilityRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->paticipantInfoGuesser->reveal(),
            $this->eventDispatcher->reveal()
        );

        $reflection = new \ReflectionClass(CreateHandler::class);
        $method     = $reflection->getMethod('truncateOvertime');
        $method->setAccessible(true);
        $method->invokeArgs($handler, [$create, &$begin, &$end]);

        $this->assertEquals($beginCheck, $begin);
        $this->assertEquals($endCheck, $end);
    }

    public function testPrepareBeginAndEnd(): void
    {
        $startTime = new \DateTime('2016-10-12 10:00:00.000');
        $endTime   = new \DateTime('2016-10-12 18:00:00.000');
        $event     = EventFactory::createEvent();
        $day       = new Day($event, $startTime, $endTime);
        $event->setDays([$day]);
        $user   = UserFactory::create();
        $sheet  = SheetFactory::create($event, $user);
        $create = new Create($event, $sheet, $user, 'fr', 'Europe/Paris');

        $expectedBegin = new \DateTime('2016-10-12 08:30:00.000');
        $expectedEnd   = new \DateTime('2016-10-12 12:00:00.000');

        $create->time['begin']['hour']   = 10;
        $create->time['begin']['minute'] = 30;

        $create->time['end']['hour']   = 14;
        $create->time['end']['minute'] = 00;

        $handler = new CreateHandler(
            $this->unavailabilityRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->paticipantInfoGuesser->reveal(),
            $this->eventDispatcher->reveal()
        );

        $reflection = new \ReflectionClass(CreateHandler::class);
        $method     = $reflection->getMethod('prepareBeginAndEnd');
        $method->setAccessible(true);
        list($begin, $end) = $method->invokeArgs($handler, [$create]);

        $this->assertEquals($expectedBegin, $begin);
        $this->assertEquals($expectedEnd, $end);
    }

    public function testPrepareBeginAndEndWithOtherTimeZoneNewYork(): void
    {
        $startTime = new \DateTime('2016-10-12 10:00:00.000');
        $endTime   = new \DateTime('2016-10-12 18:00:00.000');
        $event     = EventFactory::createEvent();
        $prefix    = EventFactory::createInvoicePrefix();
        $day       = new Day($event, $startTime, $endTime);
        $event->setDays([$day]);
        $event->update(
            $event->getTitle(),
            $event->getLocales(),
            $event->getFallback(),
            $event->getMode(),
            $event->getVat(),
            $event->getCountry(),
            $event->getCurrency(),
            'America/New_York', // -4 with UTC at that time
            $event->getDomain(),
            $event->getOrganiserName(),
            $event->getEmailTeam(),
            $prefix,
            $event->isVisible(),
            $event->isWelcomeEnabled(),
            $event->isDisabledEmailChanging(),
            $event->isDisabledPasswordChanging()
        );
        $user   = UserFactory::create();
        $sheet  = SheetFactory::create($event, $user);
        $create = new Create($event, $sheet, $user, 'fr', 'America/New_York');

        $expectedBegin = new \DateTime('2016-10-12 14:30:00.000');
        $expectedEnd   = new \DateTime('2016-10-12 18:00:00.000');

        $create->time['begin']['hour']   = 10;
        $create->time['begin']['minute'] = 30;

        $create->time['end']['hour']   = 14;
        $create->time['end']['minute'] = 00;

        $handler = new CreateHandler(
            $this->unavailabilityRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->paticipantInfoGuesser->reveal(),
            $this->eventDispatcher->reveal()
        );

        $reflection = new \ReflectionClass(CreateHandler::class);
        $method     = $reflection->getMethod('prepareBeginAndEnd');
        $method->setAccessible(true);
        list($begin, $end) = $method->invokeArgs($handler, [$create]);

        $this->assertEquals($expectedBegin, $begin);
        $this->assertEquals($expectedEnd, $end);
    }

    public function testPrepareBeginAndEndWithOtherTimeZoneLondon(): void
    {
        $startTime = new \DateTime('2016-10-12 10:00:00.000');
        $endTime   = new \DateTime('2016-10-12 18:00:00.000');
        $event     = EventFactory::createEvent();
        $prefix    = EventFactory::createInvoicePrefix();
        $day       = new Day($event, $startTime, $endTime);
        $event->setDays([$day]);
        $event->update(
            $event->getTitle(),
            $event->getLocales(),
            $event->getFallback(),
            $event->getMode(),
            $event->getVat(),
            $event->getCountry(),
            $event->getCurrency(),
            'Europe/London', // -1
            $event->getDomain(),
            $event->getOrganiserName(),
            $event->getEmailTeam(),
            $prefix,
            $event->isVisible(),
            $event->isWelcomeEnabled(),
            $event->isDisabledEmailChanging(),
            $event->isDisabledPasswordChanging()
        );
        $user   = UserFactory::create();
        $sheet  = SheetFactory::create($event, $user);
        $create = new Create($event, $sheet, $user, 'fr', 'Europe/London');

        $expectedBegin = new \DateTime('2016-10-12 09:30:00.000');
        $expectedEnd   = new \DateTime('2016-10-12 13:00:00.000');

        $create->time['begin']['hour']   = 10;
        $create->time['begin']['minute'] = 30;

        $create->time['end']['hour']   = 14;
        $create->time['end']['minute'] = 00;

        $handler = new CreateHandler(
            $this->unavailabilityRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->paticipantInfoGuesser->reveal(),
            $this->eventDispatcher->reveal()
        );

        $reflection = new \ReflectionClass(CreateHandler::class);
        $method     = $reflection->getMethod('prepareBeginAndEnd');
        $method->setAccessible(true);
        list($begin, $end) = $method->invokeArgs($handler, [$create]);

        $this->assertEquals($expectedBegin, $begin);
        $this->assertEquals($expectedEnd, $end);
    }

    public function testPrepareBeginAndEndWithString(): void
    {
        $startTime = new \DateTime('2016-10-12 10:00:00.000');
        $endTime   = new \DateTime('2016-10-12 18:00:00.000');
        $event     = EventFactory::createEvent();
        $day       = new Day($event, $startTime, $endTime);
        $event->setDays([$day]);
        $user   = UserFactory::create();
        $sheet  = SheetFactory::create($event, $user);
        $create = new Create($event, $sheet, $user, 'fr', 'Europe/Paris');

        $expectedBegin = new \DateTime('2016-10-12 08:30:00.000');
        $expectedEnd   = new \DateTime('2016-10-12 12:00:00.000');

        $create->time['begin']['hour']   = '10';
        $create->time['begin']['minute'] = '30';

        $create->time['end']['hour']   = '14';
        $create->time['end']['minute'] = '00';

        $handler = new CreateHandler(
            $this->unavailabilityRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->paticipantInfoGuesser->reveal(),
            $this->eventDispatcher->reveal()
        );

        $reflection = new \ReflectionClass(CreateHandler::class);
        $method     = $reflection->getMethod('prepareBeginAndEnd');
        $method->setAccessible(true);
        list($begin, $end) = $method->invokeArgs($handler, [$create]);

        $this->assertEquals($expectedBegin, $begin);
        $this->assertEquals($expectedEnd, $end);
    }

    public function testPrepareBeginAndEndWithDifferentDayBegin()
    {
        $startTime = new \DateTime('2016-10-11 23:00:00.000');
        $endTime   = new \DateTime('2016-10-12 18:00:00.000');
        $event     = EventFactory::createEvent();
        $day       = new Day($event, $startTime, $endTime);
        $event->setDays([$day]);
        $user   = UserFactory::create();
        $sheet  = SheetFactory::create($event, $user);
        $create = new Create($event, $sheet, $user, 'fr', 'Europe/Paris');

        $expectedBegin = new \DateTime('2016-10-11 23:05:00.000');
        $expectedEnd   = new \DateTime('2016-10-12 18:00:00.000');

        $create->time['begin']['hour']   = '1';
        $create->time['begin']['minute'] = '05';

        $create->time['end']['hour']   = '20';
        $create->time['end']['minute'] = '00';

        $handler = new CreateHandler(
            $this->unavailabilityRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->paticipantInfoGuesser->reveal(),
            $this->eventDispatcher->reveal()
        );

        $reflection = new \ReflectionClass(CreateHandler::class);
        $method     = $reflection->getMethod('prepareBeginAndEnd');
        $method->setAccessible(true);
        list($begin, $end) = $method->invokeArgs($handler, [$create]);

        $this->assertEquals($expectedBegin, $begin);
        $this->assertEquals($expectedEnd, $end);
    }

    public function testPrepareBeginAndEndWithDifferentDayEnd(): void
    {
        $startTime = new \DateTime('2016-10-11 11:00:00.000');
        $endTime   = new \DateTime('2016-10-12 02:00:00.000');
        $event     = EventFactory::createEvent();
        $prefix    = EventFactory::createInvoicePrefix();
        $day       = new Day($event, $startTime, $endTime);
        $event->setDays([$day]);
        $event->update(
            $event->getTitle(),
            $event->getLocales(),
            $event->getFallback(),
            $event->getMode(),
            $event->getVat(),
            $event->getCountry(),
            $event->getCurrency(),
            'America/New_York', // -4 with UTC at that time
            $event->getDomain(),
            $event->getOrganiserName(),
            $event->getEmailTeam(),
            $prefix,
            $event->isVisible(),
            $event->isWelcomeEnabled(),
            $event->isDisabledEmailChanging(),
            $event->isDisabledPasswordChanging()
        );
        $user   = UserFactory::create();
        $sheet  = SheetFactory::create($event, $user);
        $create = new Create($event, $sheet, $user, 'fr', 'America/New_York');

        $expectedBegin = new \DateTime('2016-10-11 11:05:00.000');
        $expectedEnd   = new \DateTime('2016-10-12 01:00:00.000');

        $create->time['begin']['hour']   = '07';
        $create->time['begin']['minute'] = '05';

        $create->time['end']['hour']   = '21';
        $create->time['end']['minute'] = '00';

        $handler = new CreateHandler(
            $this->unavailabilityRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->paticipantInfoGuesser->reveal(),
            $this->eventDispatcher->reveal()
        );

        $reflection = new \ReflectionClass(CreateHandler::class);
        $method     = $reflection->getMethod('prepareBeginAndEnd');
        $method->setAccessible(true);
        list($begin, $end) = $method->invokeArgs($handler, [$create]);

        $this->assertEquals($expectedBegin, $begin);
        $this->assertEquals($expectedEnd, $end);
    }

    public function testPrepareBeginAndEndWithDifferentDayEndAndLargeTimeZone(): void
    {
        $startTime = new \DateTime('2016-10-11 18:00:00.000');
        $endTime   = new \DateTime('2016-10-12 04:00:00.000');
        $event     = EventFactory::createEvent();
        $prefix    = EventFactory::createInvoicePrefix();
        $day       = new Day($event, $startTime, $endTime);
        $event->setDays([$day]);
        $event->update(
            $event->getTitle(),
            $event->getLocales(),
            $event->getFallback(),
            $event->getMode(),
            $event->getVat(),
            $event->getCountry(),
            $event->getCurrency(),
            'America/Los_Angeles', // -7 with UTC at that time
            $event->getDomain(),
            $event->getOrganiserName(),
            $event->getEmailTeam(),
            $prefix,
            $event->isVisible(),
            $event->isWelcomeEnabled(),
            $event->isDisabledEmailChanging(),
            $event->isDisabledPasswordChanging()
        );
        $user   = UserFactory::create();
        $sheet  = SheetFactory::create($event, $user);
        $create = new Create($event, $sheet, $user, 'fr', 'America/Los_Angeles');

        $expectedBegin = new \DateTime('2016-10-11 18:05:00.000');
        $expectedEnd   = new \DateTime('2016-10-12 01:00:00.000');

        $create->time['begin']['hour']   = '11';
        $create->time['begin']['minute'] = '05';

        $create->time['end']['hour']   = '18';
        $create->time['end']['minute'] = '00';

        $handler = new CreateHandler(
            $this->unavailabilityRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->paticipantInfoGuesser->reveal(),
            $this->eventDispatcher->reveal()
        );

        $reflection = new \ReflectionClass(CreateHandler::class);
        $method     = $reflection->getMethod('prepareBeginAndEnd');
        $method->setAccessible(true);
        list($begin, $end) = $method->invokeArgs($handler, [$create]);

        $this->assertEquals($expectedBegin, $begin);
        $this->assertEquals($expectedEnd, $end);
    }

    public function testHandleNoParticipantException(): void
    {
        $this->expectException(NoParticipantSelectedException::class);

        $startTime = new \DateTime('2016-10-11 18:00:00.000');
        $endTime   = new \DateTime('2016-10-12 04:00:00.000');
        $event     = EventFactory::createEvent();
        $day       = new Day($event, $startTime, $endTime);
        $event->setDays([$day]);
        $user                 = UserFactory::create();
        $sheet                = SheetFactory::create($event, $user);
        $create               = new Create($event, $sheet, $user, 'fr', 'Europe/Paris');
        $create->participants = [];

        $handler = new CreateHandler(
            $this->unavailabilityRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->paticipantInfoGuesser->reveal(),
            $this->eventDispatcher->reveal()
        );
        $handler->handle($create);
    }

    public function testHandleCorrect(): void
    {
        $startTime = new \DateTime('2016-10-12 08:00:00.000');
        $endTime   = new \DateTime('2016-10-12 18:00:00.000');
        $event     = EventFactory::createEvent();
        $day       = new Day($event, $startTime, $endTime);
        $event->setDays([$day]);
        $user                 = UserFactory::create();
        $sheet                = SheetFactory::create($event, $user);
        $participant          = ParticipantFactory::create($sheet, $user);
        $create               = new Create($event, $sheet, $user, 'fr', 'Europe/Paris');
        $create->participants = [$participant];

        $expectedBegin = new \DateTime('2016-10-12 08:30:00.000');
        $expectedEnd   = new \DateTime('2016-10-12 12:00:00.000');

        $create->time['begin']['hour']   = 10;
        $create->time['begin']['minute'] = 30;

        $create->time['end']['hour']   = 14;
        $create->time['end']['minute'] = 00;

        $unavailability = new Unavailability($user, $event, $expectedBegin, $expectedEnd);

        $this->participantRepository
            ->getParticipantsWithoutMeetingAndHappening([$participant], $expectedBegin, $expectedEnd)
            ->shouldBeCalled()
            ->willReturn([$participant]);

        $this->unavailabilityRepository
            ->getOverlapUnavailabilities($unavailability)
            ->shouldBeCalled()
            ->willReturn([]);

        $this->unavailabilityRepository
            ->remove($unavailability)
            ->shouldNotBeCalled();

        $this->unavailabilityRepository
            ->add($unavailability)
            ->shouldBeCalled();

        $this
            ->eventDispatcher
            ->dispatch(Events::UNAVAILABILITY_ADDED, new AddUnavailabilityEvent($user, $event))
            ->shouldBeCalled();

        $handler = new CreateHandler(
            $this->unavailabilityRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->paticipantInfoGuesser->reveal(),
            $this->eventDispatcher->reveal()
        );
        $handler->handle($create);
    }

    public function testHandleWithMessage(): void
    {
        $startTime = new \DateTime('2016-10-12 08:00:00.000');
        $endTime   = new \DateTime('2016-10-12 18:00:00.000');
        $event     = EventFactory::createEvent();
        $day       = new Day($event, $startTime, $endTime);
        $event->setDays([$day]);
        $user                 = UserFactory::create();
        $sheet                = SheetFactory::create($event, $user);
        $participant          = ParticipantFactory::create($sheet, $user);
        $create               = new Create($event, $sheet, $user, 'fr', 'Europe/Paris');
        $create->participants = [$participant];
        $create->message      = 'Ceci est un message de test';

        $expectedBegin = new \DateTime('2016-10-12 08:30:00.000');
        $expectedEnd   = new \DateTime('2016-10-12 12:00:00.000');

        $create->time['begin']['hour']   = 10;
        $create->time['begin']['minute'] = 30;

        $create->time['end']['hour']   = 14;
        $create->time['end']['minute'] = 00;

        $unavailability = new Unavailability($user, $event, $expectedBegin, $expectedEnd, 'Ceci est un message de test');

        $this->participantRepository
            ->getParticipantsWithoutMeetingAndHappening([$participant], $expectedBegin, $expectedEnd)
            ->shouldBeCalled()
            ->willReturn([$participant]);

        $this->unavailabilityRepository
            ->getOverlapUnavailabilities($unavailability)
            ->shouldBeCalled()
            ->willReturn([]);

        $this->unavailabilityRepository
            ->remove($unavailability)
            ->shouldNotBeCalled();

        $this->unavailabilityRepository
            ->add($unavailability)
            ->shouldBeCalled();

        $this
            ->eventDispatcher
            ->dispatch(Events::UNAVAILABILITY_ADDED, new AddUnavailabilityEvent($user, $event))
            ->shouldBeCalled()
        ;

        $handler = new CreateHandler(
            $this->unavailabilityRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->paticipantInfoGuesser->reveal(),
            $this->eventDispatcher->reveal()
        );
        $handler->handle($create);
    }

    public function testHandleCorrectWithRemove(): void
    {
        $startTime = new \DateTime('2016-10-12 08:00:00.000');
        $endTime   = new \DateTime('2016-10-12 18:00:00.000');
        $event     = EventFactory::createEvent();
        $day       = new Day($event, $startTime, $endTime);
        $event->setDays([$day]);
        $user                 = UserFactory::create();
        $sheet                = SheetFactory::create($event, $user);
        $participant          = ParticipantFactory::create($sheet, $user);
        $create               = new Create($event, $sheet, $user, 'fr', 'Europe/Paris');
        $create->participants = [$participant];

        $expectedBegin  = new \DateTime('2016-10-12 08:30:00.000');
        $expectedBegin2 = new \DateTime('2016-10-12 09:30:00.000');
        $expectedEnd    = new \DateTime('2016-10-12 12:00:00.000');
        $expectedEnd2   = new \DateTime('2016-10-12 14:00:00.000');

        $create->time['begin']['hour']   = 10;
        $create->time['begin']['minute'] = 30;

        $create->time['end']['hour']   = 14;
        $create->time['end']['minute'] = 00;

        $unavailability         = new Unavailability($user, $event, $expectedBegin, $expectedEnd);
        $unavailability2        = new Unavailability($user, $event, $expectedBegin2, $expectedEnd2);
        $expectedUnavailability = new Unavailability($user, $event, $expectedBegin, $expectedEnd2);

        $this->participantRepository
            ->getParticipantsWithoutMeetingAndHappening([$participant], $expectedBegin, $expectedEnd)
            ->shouldBeCalled()
            ->willReturn([$participant]);

        $this->unavailabilityRepository
            ->getOverlapUnavailabilities($unavailability)
            ->shouldBeCalled()
            ->willReturn([$unavailability2]);

        $this->unavailabilityRepository
            ->remove($unavailability2)
            ->shouldBeCalled();

        $this->unavailabilityRepository
            ->add($expectedUnavailability)
            ->shouldBeCalled();

        $this
            ->eventDispatcher
            ->dispatch(Events::UNAVAILABILITY_ADDED, new AddUnavailabilityEvent($user, $event))
            ->shouldBeCalled();

        $handler = new CreateHandler(
            $this->unavailabilityRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->paticipantInfoGuesser->reveal(),
            $this->eventDispatcher->reveal()
        );
        $handler->handle($create);
    }

    public function testHandleExceptionSheetDoesNotAttendEvent(): void
    {
        $this->expectException(CanNotCreateUnavailabilityException::class);

        $startTime = new \DateTime('2016-10-12 08:00:00.000');
        $endTime   = new \DateTime('2016-10-12 18:00:00.000');
        $event     = EventFactory::createEvent();
        $day       = new Day($event, $startTime, $endTime);
        $event->setDays([$day]);
        $user                 = UserFactory::create();
        $sheet                = SheetFactory::create($event, $user);
        $sheet->setAttendance(false);
        $participant          = ParticipantFactory::create($sheet, $user);
        $create               = new Create($event, $sheet, $user, 'fr', 'Europe/Paris');
        $create->participants = [$participant];

        $expectedBegin  = new \DateTime('2016-10-12 08:30:00.000');
        $expectedBegin2 = new \DateTime('2016-10-12 09:30:00.000');
        $expectedEnd    = new \DateTime('2016-10-12 12:00:00.000');
        $expectedEnd2   = new \DateTime('2016-10-12 14:00:00.000');

        $create->time['begin']['hour']   = 10;
        $create->time['begin']['minute'] = 30;

        $create->time['end']['hour']   = 14;
        $create->time['end']['minute'] = 00;

        $unavailability         = new Unavailability($user, $event, $expectedBegin, $expectedEnd);
        $unavailability2        = new Unavailability($user, $event, $expectedBegin2, $expectedEnd2);
        $expectedUnavailability = new Unavailability($user, $event, $expectedBegin, $expectedEnd2);

        $this->participantRepository
            ->getParticipantsWithoutMeetingAndHappening([$participant], $expectedBegin, $expectedEnd)
            ->shouldNotBeCalled()
            ->willReturn([$participant]);

        $this->unavailabilityRepository
            ->getOverlapUnavailabilities($unavailability)
            ->shouldNotBeCalled()
            ->willReturn([$unavailability2]);

        $this->unavailabilityRepository
            ->remove($unavailability2)
            ->shouldNotBeCalled();

        $this->unavailabilityRepository
            ->add($expectedUnavailability)
            ->shouldNotBeCalled();

        $handler = new CreateHandler(
            $this->unavailabilityRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->paticipantInfoGuesser->reveal(),
            $this->eventDispatcher->reveal()
        );
        $handler->handle($create);
    }

    public function testHandleSystemUnavailabilityAlreadyExistsSideToNewOne(): void
    {
        $day = $this->prophesize(Day::class);
        $day->getDay()->willReturn(new \DateTime('2018-04-02 08:00:00.000'));
        $day->getStartTime()->willReturn(new \DateTime('2018-04-02 08:00:00.000'));
        $day->getEndTime()->willReturn(new \DateTime('2018-04-02 18:00:00.000'));

        $event = $this->prophesize(Event::class);
        $event->getFirstDay()->willReturn($day->reveal());
        $event->getTimeZone()->willReturn('UTC');

        $sheet = $this->prophesize(Sheet::class);
        $sheet->attend()->willReturn(true);
        $sheet->getEvent()->willReturn($event->reveal());

        $user = $this->prophesize(User::class);
        $participant = $this->prophesize(Participant::class);
        $participant->getUser()->willReturn($user->reveal());
        $participant->getSheet()->willReturn($sheet->reveal());

        $sheet->getUserParticipant($user->reveal())->willReturn($participant->reveal());

        $create = new Create($event->reveal(), $sheet->reveal(), $user->reveal(), 'fr', 'UTC');
        $create->participants = [$participant->reveal()];
        $create->time = [
            'begin' => [
                'hour' => 10,
                'minute' => 30,
            ],
            'end' => [
                'hour' => 11,
                'minute' => 00,
            ]
        ];

        $expectedBegin = new \DateTime('2018-04-02 10:30:00.000');
        $expectedEnd = new \DateTime('2018-04-02 11:00:00.000');
        $expectedUnavailability = new Unavailability($user->reveal(), $event->reveal(), $expectedBegin, $expectedEnd);

        $overlapNotUserUnavailability1 = new Unavailability(
            $user->reveal(),
            $event->reveal(),
            new \DateTime('2018-04-02 09:30:00.000'),
            new \DateTime('2018-04-02 10:30:00.000'), // System unavailability ends when new Unavailability begins
            null,
            Unavailability::CREATED_BY_SYSTEM
        );
        $overlapNotUserUnavailability2 = new Unavailability(
            $user->reveal(),
            $event->reveal(),
            new \DateTime('2018-04-02 11:00:00.000'), // System unavailability begins when new Unavailability ends
            new \DateTime('2018-04-02 11:10:00.000'),
            null,
            Unavailability::CREATED_BY_SYSTEM
        );
        $this
            ->unavailabilityRepository
            ->getOverlapUnavailabilities($expectedUnavailability)
            ->shouldBeCalled()
            ->willReturn([$overlapNotUserUnavailability1, $overlapNotUserUnavailability2])
        ;

        $this
            ->participantRepository
            ->getParticipantsWithoutMeetingAndHappening([$participant->reveal()], $expectedBegin, $expectedEnd)
            ->shouldBeCalled()
            ->willReturn([$participant->reveal()])
        ;

        $this
            ->unavailabilityRepository
            ->add($expectedUnavailability)
            ->shouldBeCalled()
        ;

        $handler = new CreateHandler(
            $this->unavailabilityRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->paticipantInfoGuesser->reveal(),
            $this->eventDispatcher->reveal()
        );
        $handler->handle($create);
    }

    public function testParticipantsWithUnavailabilityException(): void
    {
        $this->expectException(ParticipantsWithUnavailabilityException::class);

        $day = $this->prophesize(Day::class);
        $day->getDay()->willReturn(new \DateTime('2018-04-02 08:00:00.000'));
        $day->getStartTime()->willReturn(new \DateTime('2018-04-02 08:00:00.000'));
        $day->getEndTime()->willReturn(new \DateTime('2018-04-02 18:00:00.000'));

        $event = $this->prophesize(Event::class);
        $event->getFirstDay()->willReturn($day->reveal());
        $event->getTimeZone()->willReturn('UTC');

        $sheet = $this->prophesize(Sheet::class);
        $sheet->attend()->willReturn(true);
        $sheet->getEvent()->willReturn($event->reveal());

        $user = $this->prophesize(User::class);
        $participant = $this->prophesize(Participant::class);
        $participant->getUser()->willReturn($user->reveal());
        $participant->getSheet()->willReturn($sheet->reveal());

        $sheet->getUserParticipant($user->reveal())->willReturn($participant->reveal());

        $create = new Create($event->reveal(), $sheet->reveal(), $user->reveal(), 'fr', 'UTC');
        $create->participants = [$participant->reveal()];
        $create->time = [
            'begin' => [
                'hour' => 10,
                'minute' => 30,
            ],
            'end' => [
                'hour' => 11,
                'minute' => 00,
            ]
        ];

        $expectedBegin = new \DateTime('2018-04-02 10:30:00.000');
        $expectedEnd = new \DateTime('2018-04-02 11:00:00.000');
        $expectedUnavailability = new Unavailability($user->reveal(), $event->reveal(), $expectedBegin, $expectedEnd);

        $overlapNotUserUnavailability = new Unavailability(
            $user->reveal(),
            $event->reveal(),
            new \DateTime('2018-04-02 10:45:00.000'),
            new \DateTime('2018-04-02 11:10:00.000'),
            null,
            Unavailability::CREATED_BY_SYSTEM
        );
        $this
            ->unavailabilityRepository
            ->getOverlapUnavailabilities($expectedUnavailability)
            ->shouldBeCalled()
            ->willReturn([$overlapNotUserUnavailability])
        ;

        $this
            ->participantRepository
            ->getParticipantsWithoutMeetingAndHappening([$participant->reveal()], $expectedBegin, $expectedEnd)
            ->shouldBeCalled()
            ->willReturn([$participant->reveal()])
        ;

        $this
            ->unavailabilityRepository
            ->add($expectedUnavailability)
            ->shouldNotBeCalled()
        ;

        $handler = new CreateHandler(
            $this->unavailabilityRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->paticipantInfoGuesser->reveal(),
            $this->eventDispatcher->reveal()
        );
        $handler->handle($create);
    }

    public function testPrepareBeginAndEndWithTimezoneDifferentThanEvent(): void
    {
        $startTime = new \DateTime('2016-10-11 11:00:00.000');
        $endTime   = new \DateTime('2016-10-12 02:00:00.000');
        $event     = EventFactory::createEvent();
        $day       = new Day($event, $startTime, $endTime);
        $event->setDays([$day]);
        $user   = UserFactory::create();
        $sheet  = SheetFactory::create($event, $user);
        $create = new Create($event, $sheet, $user, 'fr', 'America/New_York');

        $expectedBegin = new \DateTime('2016-10-11 11:05:00.000');
        $expectedEnd   = new \DateTime('2016-10-12 01:00:00.000');

        $create->time['begin']['hour']   = '07';
        $create->time['begin']['minute'] = '05';

        $create->time['end']['hour']   = '21';
        $create->time['end']['minute'] = '00';

        $handler = new CreateHandler(
            $this->unavailabilityRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->paticipantInfoGuesser->reveal(),
            $this->eventDispatcher->reveal()
        );

        $reflection = new \ReflectionClass(CreateHandler::class);
        $method     = $reflection->getMethod('prepareBeginAndEnd');
        $method->setAccessible(true);
        list($begin, $end) = $method->invokeArgs($handler, [$create]);

        $this->assertEquals($expectedBegin, $begin);
        $this->assertEquals($expectedEnd, $end);
    }
}
