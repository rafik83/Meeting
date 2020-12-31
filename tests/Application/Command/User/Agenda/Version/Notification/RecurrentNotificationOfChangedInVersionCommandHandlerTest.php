<?php

namespace Proximum\Vimeet\Tests\Application\Command\User\Agenda\Version\Notification;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\Notification\NotifyUserOfChangedVersionCommand;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\Notification\NotifyUserOfChangedVersionCommandHandler;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\Notification\RecurrentNotificationOfChangedInVersionCommand;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\Notification\RecurrentNotificationOfChangedInVersionCommandHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PlannerJob;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\PlannerJobRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class RecurrentNotificationOfChangedInVersionCommandHandlerTest extends TestCase
{
    /** @var \DateTime */
    private $dateTime;

    /** @var ObjectProphecy */
    private $event1, $event2, $event3, $user1, $user2, $extraDataRepository, $notifyUserOfChangedVersionCommandHandler, $plannerJobRepository;

    /** @var ObjectProphecy[] */
    private $events, $extraData;

    /** @var int */
    private $agendaVersionDiffNotificationTimeInMinutesParameters, $agendaVersionDiffDDayNotificationTimeInMinutesParameters;

    public function setUp()
    {
        $this->dateTime = new \DateTime('2018-10-10 10:00:00.000');
        $this->event1 = $this->prophesize(Event::class);
        $this->event2 = $this->prophesize(Event::class);
        $this->event3 = $this->prophesize(Event::class);
        $this->events = [
            $this->event1->reveal(),
            $this->event2->reveal(),
            $this->event3->reveal(),
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
        $this->notifyUserOfChangedVersionCommandHandler = $this->prophesize(
            NotifyUserOfChangedVersionCommandHandler::class
        );
        $this->plannerJobRepository = $this->prophesize(PlannerJobRepositoryInterface::class);
    }

    public function testHandle()
    {
        $date = new \DateTime('2018-10-10 09:30:00.000');
        $this->extraDataRepository
            ->getForEventsAndNameWithOlderThanDate(
                [$this->event1->reveal(), $this->event2->reveal()],
                Type::PLANNING_MODIFIED,
                $date
            )
            ->shouldBeCalled()
            ->willReturn($this->extraData);

        $this->plannerJobRepository->findLastByEvent($this->event1->reveal())->willReturn(null);

        $plannerJobEvent2 = $this->prophesize(PlannerJob::class);
        $plannerJobEvent2->isCompleted()->shouldBeCalled()->willReturn(true);
        $this->plannerJobRepository->findLastByEvent($this->event2->reveal())->willReturn($plannerJobEvent2->reveal());

        $plannerJobEvent3 = $this->prophesize(PlannerJob::class);
        $plannerJobEvent3->isCompleted()->shouldBeCalled()->willReturn(false);
        $this->plannerJobRepository->findLastByEvent($this->event3->reveal())->willReturn($plannerJobEvent3->reveal());

        $this->notifyUserOfChangedVersionCommandHandler
            ->handle(new NotifyUserOfChangedVersionCommand($this->event1->reveal(), $this->user1->reveal()))
            ->shouldBeCalled();

        $this->notifyUserOfChangedVersionCommandHandler
            ->handle(new NotifyUserOfChangedVersionCommand($this->event1->reveal(), $this->user2->reveal()))
            ->shouldBeCalled();

        $this->notifyUserOfChangedVersionCommandHandler
            ->handle(new NotifyUserOfChangedVersionCommand($this->event2->reveal(), $this->user2->reveal()))
            ->shouldBeCalled();

        $handler = new RecurrentNotificationOfChangedInVersionCommandHandler(
            $this->extraDataRepository->reveal(),
            $this->agendaVersionDiffNotificationTimeInMinutesParameters,
            $this->agendaVersionDiffDDayNotificationTimeInMinutesParameters,
            $this->notifyUserOfChangedVersionCommandHandler->reveal(),
            $this->dateTime,
            $this->plannerJobRepository->reveal()
        );

        $handler->handle(new RecurrentNotificationOfChangedInVersionCommand($this->events, false));
    }

    public function testHandleDDay()
    {
        $date = new \DateTime('2018-10-10 09:50:00.000');
        $this->extraDataRepository
            ->getForEventsAndNameWithOlderThanDate(
                [$this->event1->reveal(), $this->event2->reveal(), $this->event3->reveal()],
                Type::PLANNING_MODIFIED,
                $date
            )
            ->shouldBeCalled()
            ->willReturn($this->extraData);

        $this->plannerJobRepository->findLastByEvent($this->event1->reveal())->willReturn(null);
        $this->plannerJobRepository->findLastByEvent($this->event2->reveal())->willReturn(null);
        $this->plannerJobRepository->findLastByEvent($this->event3->reveal())->willReturn(null);

        $this->notifyUserOfChangedVersionCommandHandler
            ->handle(new NotifyUserOfChangedVersionCommand($this->event1->reveal(), $this->user1->reveal()))
            ->shouldBeCalled();

        $this->notifyUserOfChangedVersionCommandHandler
            ->handle(new NotifyUserOfChangedVersionCommand($this->event1->reveal(), $this->user2->reveal()))
            ->shouldBeCalled();

        $this->notifyUserOfChangedVersionCommandHandler
            ->handle(new NotifyUserOfChangedVersionCommand($this->event2->reveal(), $this->user2->reveal()))
            ->shouldBeCalled();

        $handler = new RecurrentNotificationOfChangedInVersionCommandHandler(
            $this->extraDataRepository->reveal(),
            $this->agendaVersionDiffNotificationTimeInMinutesParameters,
            $this->agendaVersionDiffDDayNotificationTimeInMinutesParameters,
            $this->notifyUserOfChangedVersionCommandHandler->reveal(),
            $this->dateTime,
            $this->plannerJobRepository->reveal()
        );

        $handler->handle(new RecurrentNotificationOfChangedInVersionCommand($this->events, true));
    }
}
