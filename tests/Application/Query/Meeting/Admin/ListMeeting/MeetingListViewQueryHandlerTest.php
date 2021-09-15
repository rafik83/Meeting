<?php

namespace Proximum\Vimeet\Tests\Application\Query\Meeting\Admin\ListMeeting;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Meeting\Admin\ListMeeting\MeetingListViewQuery;
use Proximum\Vimeet\Application\Query\Meeting\Admin\ListMeeting\MeetingListViewQueryHandler;
use Proximum\Vimeet\Application\Query\Meeting\Admin\ListMeeting\MeetingViewQuery;
use Proximum\Vimeet\Application\Query\Meeting\Admin\ListMeeting\MeetingViewQueryHandler;
use Proximum\Vimeet\Application\View\Meeting\Admin\ListMeeting\MeetingListView;
use Proximum\Vimeet\Application\View\Meeting\Admin\ListMeeting\MeetingView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;

class MeetingListViewQueryHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $event = $this->prophesize(Event::class);
        $slot = $this->prophesize(MeetingSlot::class);
        $meetingA = $this->prophesize(Meeting::class);
        $meetingB = $this->prophesize(Meeting::class);
        $meetingC = $this->prophesize(Meeting::class);

        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingViewQueryHandler = $this->prophesize(MeetingViewQueryHandler::class);

        $meetingRepository->countByEvent($event->reveal())->shouldBeCalled()->willReturn(120);
        $meetingRepository->findByMeetingSlot($slot->reveal())->shouldBeCalled()->willReturn([
            $meetingA->reveal(),
            $meetingB->reveal(),
            $meetingC->reveal(),
        ]);

        $mv1 = new MeetingView(
            1,
            'ZSpot',
            1,
            'sheetTitleA',
            [],
            2,
            'sheetTitleB',
            [],
            Meeting::STATUS_NOT_CONFIRMED
        );
        $mv2 = new MeetingView(
            2,
            'ASpot',
            3,
            'sheetTitleC',
            [],
            4,
            'sheetTitleD',
            [],
            Meeting::STATUS_CANCELED
        );
        $mv3 = new MeetingView(
            3,
            'MSpot',
            5,
            'sheetTitleE',
            [],
            6,
            'sheetTitleF',
            [],
            Meeting::STATUS_CONFIRMED
        );

        $meetingViewQueryHandler
            ->handle(new MeetingViewQuery($meetingA->reveal(), 'fr'))
            ->shouldBeCalled()
            ->willReturn($mv1)
        ;
        $meetingViewQueryHandler
            ->handle(new MeetingViewQuery($meetingB->reveal(), 'fr'))
            ->shouldBeCalled()
            ->willReturn($mv2)
        ;
        $meetingViewQueryHandler
            ->handle(new MeetingViewQuery($meetingC->reveal(), 'fr'))
            ->shouldBeCalled()
            ->willReturn($mv3)
        ;

        $query = new MeetingListViewQuery($event->reveal(), 'fr', $slot->reveal());
        $handler = new MeetingListViewQueryHandler(
            $meetingRepository->reveal(),
            $meetingViewQueryHandler->reveal()
        );

        $result = $handler->handle($query);

        $expected = new MeetingListView(120, $slot->reveal());
        $expected->addMeetingView($mv2);
        $expected->addMeetingView($mv3);
        $expected->addMeetingView($mv1);

        $this->assertEquals($expected, $result);
    }
}
