<?php

namespace Proximum\Vimeet\Tests\Application\Query\MeetingRequest\Export;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Query\MeetingRequest\Export\MeetingRequestViewQuery;
use Proximum\Vimeet\Application\Query\MeetingRequest\Export\MeetingRequestViewQueryHandler;
use Proximum\Vimeet\Application\Query\MeetingRequest\Export\SheetViewQuery;
use Proximum\Vimeet\Application\Query\MeetingRequest\Export\SheetViewQueryHandler;
use Proximum\Vimeet\Application\View\MeetingRequest\Export\MeetingRequestView;
use Proximum\Vimeet\Application\View\MeetingRequest\Export\SheetView;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;

class MeetingRequestViewQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $sheetViewQueryHandler;

    public function setUp()
    {
        $this->sheetViewQueryHandler = $this->prophesize(SheetViewQueryHandler::class);
    }

    public function testHandleWithoutMeeting()
    {
        $dateTime = new \DateTime();
        $request = $this->prophesize(Request::class);
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $participant1 = $this->prophesize(Participant::class);

        $sheetView1 = $this->prophesize(SheetView::class);
        $sheetView2 = $this->prophesize(SheetView::class);

        $request->getFromSheet()->willReturn($sheet1->reveal());
        $request->getToSheet()->willReturn($sheet2->reveal());
        $request->getId()->willReturn(167);
        $request->hasMeeting()->willReturn(false);
        $request->getState()->willReturn(Request::STATE_REFUSED);
        $request->getCreatedAt()->willReturn($dateTime);
        $request->getStateUpdatedAt()->willReturn($dateTime);

        $request->getFromParticipantsArray()->willReturn([]);
        $request->getToParticipantsArray()->willReturn([$participant1->reveal()]);

        $request->getMeeting()->willReturn(null);

        // Mock
        $this->sheetViewQueryHandler
            ->handle(new SheetViewQuery($sheet1->reveal(), [], 'fr'))
            ->shouldBeCalled()
            ->willReturn($sheetView1->reveal())
        ;

        $this->sheetViewQueryHandler
            ->handle(new SheetViewQuery($sheet2->reveal(), [$participant1->reveal()], 'fr'))
            ->shouldBeCalled()
            ->willReturn($sheetView2->reveal())
        ;

        $handler = new MeetingRequestViewQueryHandler($this->sheetViewQueryHandler->reveal());
        $result = $handler->handle(new MeetingRequestViewQuery($request->reveal(), 'fr'));

        $expected = new MeetingRequestView(
            167,
            null,
            $sheetView1->reveal(),
            $sheetView2->reveal(),
            Request::STATE_REFUSED,
            $dateTime,
            $dateTime,
            null,
            null
        );
        $this->assertEquals($expected, $result);
    }

    public function testHandle()
    {
        $dateTime = new \DateTime();
        $slotBeginDate =  new \DateTime();
        $request = $this->prophesize(Request::class);
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $participant1 = $this->prophesize(Participant::class);

        $sheetView1 = $this->prophesize(SheetView::class);
        $sheetView2 = $this->prophesize(SheetView::class);

        $meeting = $this->prophesize(Meeting::class);

        $meetingSlot = $this->prophesize(MeetingSlot::class);

        $request->getFromSheet()->willReturn($sheet1->reveal());
        $request->getToSheet()->willReturn($sheet2->reveal());
        $request->getId()->willReturn(167);
        $request->hasMeeting()->willReturn(true);
        $request->getMeeting()->willReturn($meeting->reveal());
        $meeting->getId()->willReturn(53);
        $request->getState()->willReturn(Request::STATE_REFUSED);
        $request->getCreatedAt()->willReturn($dateTime);
        $request->getStateUpdatedAt()->willReturn($dateTime);

        $request->getFromParticipantsArray()->willReturn([]);
        $request->getToParticipantsArray()->willReturn([$participant1->reveal()]);

        $request->getMeeting()->willReturn($meeting->reveal());
        $meeting->getCreatedType()->willReturn(Meeting::CREATED_BY_ADMIN);
        $meeting->getSlot()->willReturn($meetingSlot);
        $meetingSlot->getBegin()->willReturn($slotBeginDate);

        // Mock
        $this->sheetViewQueryHandler
            ->handle(new SheetViewQuery($sheet1->reveal(), [], 'fr'))
            ->shouldBeCalled()
            ->willReturn($sheetView1->reveal())
        ;

        $this->sheetViewQueryHandler
            ->handle(new SheetViewQuery($sheet2->reveal(), [$participant1->reveal()], 'fr'))
            ->shouldBeCalled()
            ->willReturn($sheetView2->reveal())
        ;

        $handler = new MeetingRequestViewQueryHandler($this->sheetViewQueryHandler->reveal());
        $result = $handler->handle(new MeetingRequestViewQuery($request->reveal(), 'fr'));

        $expected = new MeetingRequestView(
            167,
            53,
            $sheetView1->reveal(),
            $sheetView2->reveal(),
            Request::STATE_PLANNED,
            $dateTime,
            $dateTime,
            Meeting::CREATED_BY_ADMIN,
            $slotBeginDate
        );
        $this->assertEquals($expected, $result);
    }
}
