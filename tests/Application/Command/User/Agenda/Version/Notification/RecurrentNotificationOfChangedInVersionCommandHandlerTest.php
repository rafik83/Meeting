<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\User\Agenda\Version\Notification;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\Notification\NotifyUserOfChangedVersionCommand;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\Notification\NotifyUserOfChangedVersionCommandHandler;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\Notification\RecurrentNotificationOfChangedInVersionCommand;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\Notification\RecurrentNotificationOfChangedInVersionCommandHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class RecurrentNotificationOfChangedInVersionCommandHandlerTest extends TestCase
{
    /** @var \DateTime */
    private $dateTime;

    /** @var ObjectProphecy */
    private $event1, $event2, $user1, $user2, $extraDataRepository, $notifyUserOfChangedVersionCommandHandler;

    /** @var ObjectProphecy[] */
    private $events, $extraData;

    /** @var int */
    private $agendaVersionDiffNotificationTimeInMinutesParameters, $agendaVersionDiffDDayNotificationTimeInMinutesParameters;

    public function setUp()
    {
        $this->dateTime = new \DateTime('2018-10-10 10:00:00.000');
        $this->event1 = $this->prophesize(Event::class);
        $this->event2 = $this->prophesize(Event::class);
        $this->events = [
            $this->event1->reveal(),
            $this->event2->reveal(),
        ];
        $this->user1 = $this->prophesize(User::class);
        $this->user2 = $this->prophesize(User::class);

        $extraData1 = $this->prophesize(ExtraData::class);
        $extraData2 = $this->prophesize(ExtraData::class);
        $extraData3 = $this->prophesize(ExtraData::class);

        $extraData1->getEvent()->shouldBeCalled()->willReturn($this->event1->reveal());
        $extraData2->getEvent()->shouldBeCalled()->willReturn($this->event1->reveal());
        $extraData3->getEvent()->shouldBeCalled()->willReturn($this->event2->reveal());

        $extraData1->getUser()->shouldBeCalled()->willReturn($this->user1->reveal());
        $extraData2->getUser()->shouldBeCalled()->willReturn($this->user2->reveal());
        $extraData3->getUser()->shouldBeCalled()->willReturn($this->user2->reveal());

        $this->extraData = [
            $extraData1->reveal(),
            $extraData2->reveal(),
            $extraData3->reveal(),
        ];

        $this->extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $this->agendaVersionDiffNotificationTimeInMinutesParameters = 30;
        $this->agendaVersionDiffDDayNotificationTimeInMinutesParameters = 10;
        $this->notifyUserOfChangedVersionCommandHandler = $this->prophesize(NotifyUserOfChangedVersionCommandHandler::class);
    }

    public function testHandle()
    {
        $date = new \DateTime('2018-10-10 09:30:00.000');
        $this->extraDataRepository->getForEventsAndNameWithOlderThanDate($this->events, Type::PLANNING_MODIFIED, $date)
            ->shouldBeCalled()
            ->willReturn($this->extraData)
        ;

        $this->notifyUserOfChangedVersionCommandHandler
            ->handle(new NotifyUserOfChangedVersionCommand($this->event1->reveal(), $this->user1->reveal()))
            ->shouldBeCalled()
        ;

        $this->notifyUserOfChangedVersionCommandHandler
            ->handle(new NotifyUserOfChangedVersionCommand($this->event1->reveal(), $this->user2->reveal()))
            ->shouldBeCalled()
        ;

        $this->notifyUserOfChangedVersionCommandHandler
            ->handle(new NotifyUserOfChangedVersionCommand($this->event2->reveal(), $this->user2->reveal()))
            ->shouldBeCalled()
        ;

        $handler = new RecurrentNotificationOfChangedInVersionCommandHandler(
            $this->extraDataRepository->reveal(),
            $this->agendaVersionDiffNotificationTimeInMinutesParameters,
            $this->agendaVersionDiffDDayNotificationTimeInMinutesParameters,
            $this->notifyUserOfChangedVersionCommandHandler->reveal(),
            $this->dateTime
        );

        $handler->handle(new RecurrentNotificationOfChangedInVersionCommand($this->events, false));
    }

    public function testHandleDDay()
    {
        $date = new \DateTime('2018-10-10 09:50:00.000');
        $this->extraDataRepository->getForEventsAndNameWithOlderThanDate($this->events, Type::PLANNING_MODIFIED, $date)
            ->shouldBeCalled()
            ->willReturn($this->extraData)
        ;

        $this->notifyUserOfChangedVersionCommandHandler
            ->handle(new NotifyUserOfChangedVersionCommand($this->event1->reveal(), $this->user1->reveal()))
            ->shouldBeCalled()
        ;

        $this->notifyUserOfChangedVersionCommandHandler
            ->handle(new NotifyUserOfChangedVersionCommand($this->event1->reveal(), $this->user2->reveal()))
            ->shouldBeCalled()
        ;

        $this->notifyUserOfChangedVersionCommandHandler
            ->handle(new NotifyUserOfChangedVersionCommand($this->event2->reveal(), $this->user2->reveal()))
            ->shouldBeCalled()
        ;

        $handler = new RecurrentNotificationOfChangedInVersionCommandHandler(
            $this->extraDataRepository->reveal(),
            $this->agendaVersionDiffNotificationTimeInMinutesParameters,
            $this->agendaVersionDiffDDayNotificationTimeInMinutesParameters,
            $this->notifyUserOfChangedVersionCommandHandler->reveal(),
            $this->dateTime
        );

        $handler->handle(new RecurrentNotificationOfChangedInVersionCommand($this->events, true));
    }
}
