<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Dashboard;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Query\Dashboard\DashboardMeetingViewQuery;
use Proximum\Vimeet\Application\Query\Dashboard\DashboardMeetingViewQueryHandler;
use Proximum\Vimeet\Application\View\Dashboard\DashboardMeetingView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;

class DashboardMeetingViewQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $meetingRepository;

    /** @var ObjectProphecy */
    private $requestRepository;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $this->requestRepository = $this->prophesize(RequestRepositoryInterface::class);
    }

    public function testHandleEventWithoutDay()
    {
        $this->event->hasDay()->shouldBeCalled()->willReturn(false);

        $this->meetingRepository->countByEvent($this->event->reveal())->shouldBeCalled()->willReturn(200);
        $this->meetingRepository->countBetweenDatesByEvent(Argument::any())->shouldNotBeCalled();
        $this->meetingRepository
            ->countCreatedByParticipantByEvent($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(0)
        ;
        $this->requestRepository->countApprovedByEvent($this->event->reveal())->shouldBeCalled()->willReturn(300);
        $this->requestRepository->countPendingByEvent($this->event->reveal())->shouldBeCalled()->willReturn(25);
        $this->requestRepository->countRefusedByEvent($this->event->reveal())->shouldBeCalled()->willReturn(250);

        $handler = new DashboardMeetingViewQueryHandler(
            $this->meetingRepository->reveal(),
            $this->requestRepository->reveal()
        );

        $result = $handler->handle(new DashboardMeetingViewQuery($this->event->reveal()));

        $expected = new DashboardMeetingView(
            200,
            0,
            0,
            300,
            25,
            250
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandle()
    {
        $day1 = $this->prophesize(Event\Day::class);
        $day2 = $this->prophesize(Event\Day::class);
        $dateTime1 = new \DateTime('2017-10-10 10:00:00');
        $dateTime2 = new \DateTime('2017-10-11 18:00:00');

        $day1->getBegin()->willReturn($dateTime1);
        $day2->getEnd()->willReturn($dateTime2);

        $this->event->hasDay()->shouldBeCalled()->willReturn(true);
        $this->event->getFirstDay()->shouldBeCalled()->willReturn($day1->reveal());
        $this->event->getLastDay()->shouldBeCalled()->willReturn($day2->reveal());

        $this->meetingRepository->countByEvent($this->event->reveal())->shouldBeCalled()->willReturn(200);
        $this->meetingRepository
            ->countBetweenDatesByEvent(
                $this->event->reveal(),
                $dateTime1,
                $dateTime2
            )
            ->shouldBeCalled()
            ->willReturn(30)
        ;
        $this->meetingRepository
            ->countCreatedByParticipantByEvent($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(15)
        ;

        $this->requestRepository->countApprovedByEvent($this->event->reveal())->shouldBeCalled()->willReturn(300);
        $this->requestRepository->countPendingByEvent($this->event->reveal())->shouldBeCalled()->willReturn(25);
        $this->requestRepository->countRefusedByEvent($this->event->reveal())->shouldBeCalled()->willReturn(250);

        $handler = new DashboardMeetingViewQueryHandler(
            $this->meetingRepository->reveal(),
            $this->requestRepository->reveal()
        );

        $result = $handler->handle(new DashboardMeetingViewQuery($this->event->reveal()));

        $expected = new DashboardMeetingView(
            200,
            30,
            15,
            300,
            25,
            250
        );

        $this->assertEquals($expected, $result);
    }
}
