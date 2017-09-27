<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\MeetingRequest;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\MeetingRequest\Remind;
use Proximum\Vimeet\Application\Command\MeetingRequest\ReminderHandler;
use Proximum\Vimeet\Application\Exception\Event\NoEventOnCurrentDayException;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

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

    public function setUp()
    {
        $this->eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $this->userRepository  = $this->prophesize(UserRepositoryInterface::class);
        $this->extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $this->dateTime = new \DateTime();
    }

    public function testHandle()
    {
        $expectedCurrentEvents = [
            EventFactory::createEvent('Michel Drucker fait son show'),
            EventFactory::createEvent('Les estivales de John Snow')
        ];

        $expectedUsers = [];

        $command = new Remind($this->dateTime);

        $this
            ->eventRepository
            ->findByDay($command->dateTime)
            ->shouldBeCalled()
            ->willReturn($expectedCurrentEvents);
        ;

        $this
            ->userRepository
            ->getUsersByEventsWithValidatedPhoneNumberAndPendingRequest($expectedCurrentEvents)
            ->shouldBeCalled()
            ->willReturn([$expectedUsers])
        ;

        $handler = new ReminderHandler(
            $this->eventRepository->reveal(),
            $this->userRepository->reveal(),
            $this->extraDataRepository->reveal()
        );

        $handler->handle($command);
    }

    public function testNoEventOnThisDayException()
    {
        $command = new Remind($this->dateTime);

        $this
            ->eventRepository
            ->findByDay($command->dateTime)
            ->shouldBeCalled()
            ->willReturn([]);
        ;

        $this->expectException(NoEventOnCurrentDayException::class);

        $this
            ->userRepository
            ->getUsersByEventsWithValidatedPhoneNumberAndPendingRequest([])
            ->shouldNotBeCalled()
        ;

        $handler = new ReminderHandler(
            $this->eventRepository->reveal(),
            $this->userRepository->reveal(),
            $this->extraDataRepository->reveal()
        );

        $handler->handle($command);
    }
}
