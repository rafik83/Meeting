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
use Proximum\Vimeet\Application\Command\Unavailability\Create;
use Proximum\Vimeet\Application\Command\Unavailability\CreateHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Unavailability\AddUnavailabilityEvent;
use Proximum\Vimeet\Application\Exception\Unavailability\CanNotCreateUnavailabilityException;
use Proximum\Vimeet\Application\Exception\Unavailability\NoParticipantSelectedException;
use Proximum\Vimeet\Application\Exception\Unavailability\ParticipantsSelectedWithMeetingOrHappeningException;
use Proximum\Vimeet\Application\Exception\Unavailability\TimeOutOfRangeException;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\Unavailability;
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
    /**
     * @var UnavailabilityRepositoryInterface
     */
    private $unavailabilityRepository;

    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @var ParticipantInfoGuesser
     */
    private $paticipantInfoGuesser;

    /** @var DelayedEventDispatcher */
    private $eventDispatcher;

    /**
     * Init mock for the suite test
     */
    public function setUp()
    {
        $this->unavailabilityRepository = $this->prophesize(UnavailabilityRepositoryInterface::class);
        $this->participantRepository    = $this->prophesize(ParticipantRepositoryInterface::class);
        $this->paticipantInfoGuesser    = $this->prophesize(ParticipantInfoGuesser::class);
        $this->eventDispatcher          = $this->prophesize(DelayedEventDispatcher::class);
    }

    public function testCheckTimeOutOfDayFunctionWithBegin()
    {
        $this->expectException(TimeOutOfRangeException::class);

        $startTime = new \DateTime('2016-10-12 10:00:00.000');
        $endTime   = new \DateTime('2016-10-12 18:00:00.000');

        $event = EventFactory::createEvent();
        $day   = new Day($event, $startTime, $endTime);
        $event->setDays([$day]);
        $user   = UserFactory::create();
        $sheet  = SheetFactory::create($event, $user);
        $create = new Create($event, $sheet, $user, 'fr');

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

    public function testCheckTimeOutOfDayFunctionWithEnd()
    {
        $this->expectException(TimeOutOfRangeException::class);

        $startTime = new \DateTime('2016-10-12 10:00:00.000');
        $endTime   = new \DateTime('2016-10-12 18:00:00.000');

        $event = EventFactory::createEvent();
        $day   = new Day($event, $startTime, $endTime);
        $event->setDays([$day]);
        $user   = UserFactory::create();
        $sheet  = SheetFactory::create($event, $user);
        $create = new Create($event, $sheet, $user, 'fr');

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

    public function testCheckTimeOutOfDayFunctionWithoutException()
    {
        $startTime = new \DateTime('2016-10-12 10:00:00.000');
        $endTime   = new \DateTime('2016-10-12 18:00:00.000');

        $event = EventFactory::createEvent();
        $day   = new Day($event, $startTime, $endTime);
        $event->setDays([$day]);
        $user   = UserFactory::create();
        $sheet  = SheetFactory::create($event, $user);
        $create = new Create($event, $sheet, $user, 'fr');

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

    public function testCheckParticipantsConflict()
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
        $create               = new Create($event, $sheet, $user, 'fr');
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

    public function testCheckParticipantsConflictWithoutConflict()
    {
        $startTime = new \DateTime('2016-10-12 10:00:00.000');
        $endTime   = new \DateTime('2016-10-12 18:00:00.000');
        $event     = EventFactory::createEvent();
        $day       = new Day($event, $startTime, $endTime);
        $event->setDays([$day]);
        $user                 = UserFactory::create();
        $sheet                = SheetFactory::create($event, $user);
        $participant          = ParticipantFactory::create($sheet, $user);
        $create               = new Create($event, $sheet, $user, 'fr');
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

    public function testTruncateOvertimeWithBeginOvertime()
    {
        $startTime = new \DateTime('2016-10-12 10:00:00.000');
        $endTime   = new \DateTime('2016-10-12 18:00:00.000');
        $event     = EventFactory::createEvent();
        $day       = new Day($event, $startTime, $endTime);
        $event->setDays([$day]);
        $user     = UserFactory::create();
        $sheet    = SheetFactory::create($event, $user);
        $create   = new Create($event, $sheet, $user, 'fr');
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

    public function testTruncateOvertimeWithEndAndBeginOvertime()
    {
        $startTime = new \DateTime('2016-10-12 10:00:00.000');
        $endTime   = new \DateTime('2016-10-12 18:00:00.000');
        $event     = EventFactory::createEvent();
        $day       = new Day($event, $startTime, $endTime);
        $event->setDays([$day]);
        $user   = UserFactory::create();
        $sheet  = SheetFactory::create($event, $user);
        $create = new Create($event, $sheet, $user, 'fr');
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

    public function testTruncateOvertimeWithNoOvertime()
    {
        $startTime = new \DateTime('2016-10-12 10:00:00.000');
        $endTime   = new \DateTime('2016-10-12 18:00:00.000');
        $event     = EventFactory::createEvent();
        $day       = new Day($event, $startTime, $endTime);
        $event->setDays([$day]);
        $user       = UserFactory::create();
        $sheet      = SheetFactory::create($event, $user);
        $create     = new Create($event, $sheet, $user, 'fr');
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

    public function testPrepareBeginAndEnd()
    {
        $startTime = new \DateTime('2016-10-12 10:00:00.000');
        $endTime   = new \DateTime('2016-10-12 18:00:00.000');
        $event     = EventFactory::createEvent();
        $day       = new Day($event, $startTime, $endTime);
        $event->setDays([$day]);
        $user   = UserFactory::create();
        $sheet  = SheetFactory::create($event, $user);
        $create = new Create($event, $sheet, $user, 'fr');

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

    public function testPrepareBeginAndEndWithOtherTimeZoneNewYork()
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
            $event->isWelcomeEnabled()
        );
        $user   = UserFactory::create();
        $sheet  = SheetFactory::create($event, $user);
        $create = new Create($event, $sheet, $user, 'fr');

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

    public function testPrepareBeginAndEndWithOtherTimeZoneLondon()
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
            $event->isWelcomeEnabled()
        );
        $user   = UserFactory::create();
        $sheet  = SheetFactory::create($event, $user);
        $create = new Create($event, $sheet, $user, 'fr');

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

    public function testPrepareBeginAndEndWithString()
    {
        $startTime = new \DateTime('2016-10-12 10:00:00.000');
        $endTime   = new \DateTime('2016-10-12 18:00:00.000');
        $event     = EventFactory::createEvent();
        $day       = new Day($event, $startTime, $endTime);
        $event->setDays([$day]);
        $user   = UserFactory::create();
        $sheet  = SheetFactory::create($event, $user);
        $create = new Create($event, $sheet, $user, 'fr');

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
        $create = new Create($event, $sheet, $user, 'fr');

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

    public function testPrepareBeginAndEndWithDifferentDayEnd()
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
            $event->isWelcomeEnabled()
        );
        $user   = UserFactory::create();
        $sheet  = SheetFactory::create($event, $user);
        $create = new Create($event, $sheet, $user, 'fr');

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

    public function testPrepareBeginAndEndWithDifferentDayEndAndLargeTimeZone()
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
            $event->isWelcomeEnabled()
        );
        $user   = UserFactory::create();
        $sheet  = SheetFactory::create($event, $user);
        $create = new Create($event, $sheet, $user, 'fr');

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

    public function testHandleNoParticipantException()
    {
        $this->expectException(NoParticipantSelectedException::class);

        $startTime = new \DateTime('2016-10-11 18:00:00.000');
        $endTime   = new \DateTime('2016-10-12 04:00:00.000');
        $event     = EventFactory::createEvent();
        $day       = new Day($event, $startTime, $endTime);
        $event->setDays([$day]);
        $user                 = UserFactory::create();
        $sheet                = SheetFactory::create($event, $user);
        $create               = new Create($event, $sheet, $user, 'fr');
        $create->participants = [];

        $handler = new CreateHandler(
            $this->unavailabilityRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->paticipantInfoGuesser->reveal(),
            $this->eventDispatcher->reveal()
        );
        $handler->handle($create);
    }

    public function testHandleCorrect()
    {
        $startTime = new \DateTime('2016-10-12 08:00:00.000');
        $endTime   = new \DateTime('2016-10-12 18:00:00.000');
        $event     = EventFactory::createEvent();
        $day       = new Day($event, $startTime, $endTime);
        $event->setDays([$day]);
        $user                 = UserFactory::create();
        $sheet                = SheetFactory::create($event, $user);
        $participant          = ParticipantFactory::create($sheet, $user);
        $create               = new Create($event, $sheet, $user, 'fr');
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

    public function testHandleWithMessage()
    {
        $startTime = new \DateTime('2016-10-12 08:00:00.000');
        $endTime   = new \DateTime('2016-10-12 18:00:00.000');
        $event     = EventFactory::createEvent();
        $day       = new Day($event, $startTime, $endTime);
        $event->setDays([$day]);
        $user                 = UserFactory::create();
        $sheet                = SheetFactory::create($event, $user);
        $participant          = ParticipantFactory::create($sheet, $user);
        $create               = new Create($event, $sheet, $user, 'fr');
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

    public function testHandleCorrectWithRemove()
    {
        $startTime = new \DateTime('2016-10-12 08:00:00.000');
        $endTime   = new \DateTime('2016-10-12 18:00:00.000');
        $event     = EventFactory::createEvent();
        $day       = new Day($event, $startTime, $endTime);
        $event->setDays([$day]);
        $user                 = UserFactory::create();
        $sheet                = SheetFactory::create($event, $user);
        $participant          = ParticipantFactory::create($sheet, $user);
        $create               = new Create($event, $sheet, $user, 'fr');
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

    public function testHandleExceptionSheetDoesNotAttendEvent()
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
        $create               = new Create($event, $sheet, $user, 'fr');
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
}
