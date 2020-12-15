<?php

namespace Proximum\Vimeet\Tests\Application\Query\Dashboard;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Query\Dashboard\DashboardMeetingViewQuery;
use Proximum\Vimeet\Application\Query\Dashboard\DashboardMeetingViewQueryHandler;
use Proximum\Vimeet\Application\Query\Dashboard\View\DashboardRequestView;
use Proximum\Vimeet\Application\View\Dashboard\DashboardMeetingView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Repository\ChatSessionRepositoryInterface;
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

    /** @var ObjectProphecy */
    private $chatSessionRepository;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $this->requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $this->chatSessionRepository = $this->prophesize(ChatSessionRepositoryInterface::class);
    }

    public function testHandleEventWithoutDay()
    {
        $this->event->hasDay()->shouldBeCalled()->willReturn(false);

        $this->meetingRepository->countByEvent($this->event->reveal())->shouldBeCalled()->willReturn(200);
        $this->meetingRepository->countBetweenDatesByEventAndType(Argument::any(), Argument::any(), Argument::any(), Meeting::CREATED_BY_ADMIN)->shouldNotBeCalled();

        $this->meetingRepository
            ->countCreatedByEventAndType($this->event->reveal(), Meeting::CREATED_BY_PARTICIPANT)
            ->shouldBeCalled()
            ->willReturn(10)
        ;
        $this->meetingRepository
            ->countCreatedByEventAndType($this->event->reveal(), Meeting::CREATED_BY_PLANNER)
            ->shouldBeCalled()
            ->willReturn(10)
        ;
        $this->meetingRepository
            ->countUpstreamByEventAndType(Argument::any())
            ->shouldNotBeCalled()
        ;
        $this->chatSessionRepository
            ->countCallVisioByEvent($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(3)
        ;

        $this->requestRepository
            ->getDashboardRequestViewsByEvent($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([
                new DashboardRequestView(3, Meeting\Request::STATE_SENT, null),
                new DashboardRequestView(3, Meeting\Request::STATE_APPROVED, 1337),
                new DashboardRequestView(7, Meeting\Request::STATE_APPROVED, null),
                new DashboardRequestView(7, Meeting\Request::STATE_REFUSED, null),
                new DashboardRequestView(7, Meeting\Request::STATE_REFUSED, null),
            ])
        ;

        $handler = new DashboardMeetingViewQueryHandler(
            $this->meetingRepository->reveal(),
            $this->requestRepository->reveal(),
            $this->chatSessionRepository->reveal()
        );

        $result = $handler->handle(new DashboardMeetingViewQuery($this->event->reveal()));

        $expected = new DashboardMeetingView(
            203,
            0,
            10,
            10,
            0,
            3,
            2,
            1,
            2,
            [
                3 => 2,
                7 => 3,
            ],
            [
                3 => [
                    Meeting\Request::STATE_SENT => 1,
                    Meeting\Request::STATE_APPROVED => 1,
                    Meeting\Request::STATE_PLANNED => 1
                ],
                7 => [
                    Meeting\Request::STATE_APPROVED => 1,
                    Meeting\Request::STATE_REFUSED => 2
                ],
            ]
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

        $this->meetingRepository->countBetweenDatesByEventAndType($this->event->reveal(), $dateTime1, $dateTime2, Meeting::CREATED_BY_ADMIN)
            ->shouldBeCalled()
            ->willReturn(20);

        $this->meetingRepository
            ->countCreatedByEventAndType(
                $this->event->reveal(),
                Meeting::CREATED_BY_PLANNER
            )
            ->shouldBeCalled()
            ->willReturn(30)
        ;
        $this->meetingRepository
            ->countCreatedByEventAndType(
                $this->event->reveal(),
                Meeting::CREATED_BY_PARTICIPANT
            )
            ->shouldBeCalled()
            ->willReturn(40)
        ;

        $this->meetingRepository
            ->countUpstreamByEventAndType(
                $this->event->reveal(),
                $dateTime1,
                Meeting::CREATED_BY_ADMIN
            )
            ->shouldBeCalled()
            ->willReturn(140);

        $this->requestRepository
            ->getDashboardRequestViewsByEvent($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([
                new DashboardRequestView(3, Meeting\Request::STATE_SENT, null),
                new DashboardRequestView(3, Meeting\Request::STATE_APPROVED, 1337),
                new DashboardRequestView(7, Meeting\Request::STATE_APPROVED, null),
                new DashboardRequestView(7, Meeting\Request::STATE_REFUSED, null),
                new DashboardRequestView(7, Meeting\Request::STATE_REFUSED, null),
            ])
        ;

        $this->chatSessionRepository
            ->countCallVisioByEvent($this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(3)
        ;

        $handler = new DashboardMeetingViewQueryHandler(
            $this->meetingRepository->reveal(),
            $this->requestRepository->reveal(),
            $this->chatSessionRepository->reveal()
        );

        $result = $handler->handle(new DashboardMeetingViewQuery($this->event->reveal()));

        $expected = new DashboardMeetingView(
            203,
            20,
            40,
            30,
            140,
            3,
            2,
            1,
            2,
            [
                3 => 2,
                7 => 3,
            ],
            [
                3 => [
                    Meeting\Request::STATE_SENT => 1,
                    Meeting\Request::STATE_APPROVED => 1,
                    Meeting\Request::STATE_PLANNED => 1
                ],
                7 => [
                    Meeting\Request::STATE_APPROVED => 1,
                    Meeting\Request::STATE_REFUSED => 2
                ],
            ]
        );

        $this->assertEquals($expected, $result);
    }
}
