<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\MeetingRequest;

use PHPUnit\Framework\Constraint\Count;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\MeetingRequest\Counter;
use Proximum\Vimeet\Application\Command\MeetingRequest\Remind;
use Proximum\Vimeet\Application\Command\MeetingRequest\ReminderHandler;
use Proximum\Vimeet\Application\Command\MeetingRequest\SmsNotification;
use Proximum\Vimeet\Application\Exception\Event\NoEventOnCurrentDayException;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class ReminderHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $eventRepository;

    /** @var ObjectProphecy */
    private $userRepository;

    /** @var ObjectProphecy */
    private $extraDataRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var ObjectProphecy */
    private $sheetRepository;

    /** @var ObjectProphecy */
    private $smsNotification;

    /** @var ObjectProphecy */
    private $counter;

    /** @var ReminderHandler */
    private $handler;

    public function setUp()
    {
        $this->eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $this->userRepository  = $this->prophesize(UserRepositoryInterface::class);
        $this->extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $this->sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $this->smsNotification = $this->prophesize(SmsNotification::class);
        $this->counter = $this->prophesize(Counter::class);
        $this->dateTime = new \DateTime();

        $this->handler = new ReminderHandler(
            $this->eventRepository->reveal(),
            $this->userRepository->reveal(),
            $this->extraDataRepository->reveal(),
            $this->sheetRepository->reveal(),
            $this->dateTime,
            $this->smsNotification->reveal(),
            $this->counter->reveal()
        );
    }

    /*
     * Use case to test :
     *
     * User qui dois être notifié (+2h)
     * User qui ne dois pas (-2h)
     * User sans proposition
     */

    public function testHandle()
    {
        $event = EventFactory::createEvent('Les estivales de Jean Neige');
        $user = UserFactory::create();

//            EventFactory::createEvent('Michel Drucker fait son show'),

        $command = new Remind();

        $this
            ->eventRepository
            ->findByDay($this->dateTime)
            ->shouldBeCalled()
            ->willReturn([$event]);
        ;

        $this
            ->userRepository
            ->getUsersByEventsWithValidatedPhoneNumberAndPendingRequest($event)
            ->shouldBeCalled()
            ->willReturn([$user])
        ;

        $this->handler->handle($command);
    }

    public function testNoEventOnThisDayException()
    {
        $this->expectException(NoEventOnCurrentDayException::class);

        $this->eventRepository->findByDay($this->dateTime)->shouldBeCalled()->willReturn([]);;
        $this->userRepository->getUsersByEventsWithValidatedPhoneNumberAndPendingRequest([])->shouldNotBeCalled();

        $this->handler->handle(new Remind());
    }
}
