<?php

namespace Proximum\Vimeet\Tests\Application\Query\Planner;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Planner\MeetingViewQuery;
use Proximum\Vimeet\Application\Query\Planner\MeetingViewQueryHandler;
use Proximum\Vimeet\Application\View\Planner\Day;
use Proximum\Vimeet\Application\View\Planner\MeetingView;
use Proximum\Vimeet\Application\View\Planner\ParticipantView;
use Proximum\Vimeet\Application\View\Planner\SheetView;
use Proximum\Vimeet\Application\View\Planner\SlotView;
use Proximum\Vimeet\Application\View\Planner\SpotView;
use Proximum\Vimeet\Application\View\Planner\TypeView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class MeetingViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        // Data
        $event      = EventFactory::createEvent();
        $typeView   = new TypeView(1, 'title');
        $sheet1     = SheetFactory::create($event);
        $sheet2     = SheetFactory::create($event);
        $sheet3     = SheetFactory::create($event);
        $sheet4     = SheetFactory::create($event);
        $sheetView  = new SheetView(1, $typeView, 2, 2);
        $sheetView2 = new SheetView(2, $typeView, 2, 2);
        $sheetView3 = new SheetView(3, $typeView, 2, 2);
        $sheets     = [$sheetView, $sheetView2, $sheetView3];

        $day       = new Day(1, 12, 10, 2016);
        $slotView  = new SlotView(1, 1, 10, 30, $day);
        $slotView2 = new SlotView(2, 2, 11, 0, $day);
        $slotView3 = new SlotView(3, 3, 11, 30, $day);
        $slots     = [$slotView, $slotView2, $slotView3];
        $spots     = [$this->prophesize(SpotView::class)->reveal()];

        $participant1     = ParticipantFactory::create($sheet1);
        $participant2     = ParticipantFactory::create($sheet1);
        $participant3     = ParticipantFactory::create($sheet2);
        $participant4     = ParticipantFactory::create($sheet3);
        $participant5     = ParticipantFactory::create($sheet3);

        $participantView  = new ParticipantView(1, 1, 'fullName1', $sheetView, [$slotView], false);
        $participantView2 = new ParticipantView(2, 2, 'fullName2', $sheetView, [], true);
        $participantView3 = new ParticipantView(3, 3, 'fullName3', $sheetView2, [$slotView2], true);
        $participantView4 = new ParticipantView(4, 4, 'fullName4', $sheetView3, [], false);
        $participantView5 = new ParticipantView(5, 5, 'fullName5', $sheetView3, [$slotView, $slotView2, $slotView3], true);
        $participants     = [
            $participantView,
            $participantView2,
            $participantView3,
            $participantView4,
            $participantView5,
        ];

        $creator  = UserFactory::create();
        $request1 = new Request($sheet1, [$participant1, $participant2], $sheet2, [$participant3], new \DateTime(), $creator, $event);
        $request2 = new Request($sheet3, [], $sheet1, [$participant1], new \DateTime(), $creator, $event);
        $request3 = new Request($sheet2, [], $sheet3, [], new \DateTime(), $creator, $event);
        $request4 = new Request($sheet4, [], $sheet3, [], new \DateTime(), $creator, $event); // Sheet not in catalog, should be escaped
        $request5 = new Request($sheet3, [], $sheet1, [$participant1], new \DateTime(), $creator, $event);
        $request6 = new Request($sheet1, [$participant2], $sheet2, [$participant3], new \DateTime(), $creator, $event);

        // Reflection
        $reflectionSheet = new \ReflectionClass(Sheet::class);
        $propertySheet   = $reflectionSheet->getProperty('id');
        $propertySheet->setAccessible(true);
        $propertySheet->setValue($sheet1, 1);
        $propertySheet->setValue($sheet2, 2);
        $propertySheet->setValue($sheet3, 3);
        $propertySheet->setAccessible(false);

        $reflection = new \ReflectionClass(Participant::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($participant1, 1);
        $property->setValue($participant2, 2);
        $property->setValue($participant3, 3);
        $property->setValue($participant4, 4);
        $property->setValue($participant5, 5);
        $property->setAccessible(false);

        $reflectionRequest = new \ReflectionClass(Request::class);
        $propertyRequest   = $reflectionRequest->getProperty('id');
        $propertyRequest->setAccessible(true);
        $propertyRequest->setValue($request1, 1);
        $propertyRequest->setValue($request2, 2);
        $propertyRequest->setValue($request3, 3);
        $propertyRequest->setValue($request4, 4);
        $propertyRequest->setValue($request5, 5);
        $propertyRequest->setValue($request6, 6);
        $propertyRequest->setAccessible(false);

        // Mock
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository->getAllAcceptedByEvent($event)->shouldBeCalled()->willReturn(
            [$request1, $request2, $request3, $request4, $request5, $request6]
        );

        // Handler
        $handler = new MeetingViewQueryHandler($requestRepository->reveal());
        $result  = $handler->handle(new MeetingViewQuery($event, $sheets, $participants, $slots, $spots, 'moving_allowed'));

        // Expected
        $expected = [
            new MeetingView(1, [$sheetView, $sheetView2], [$participantView, $participantView2, $participantView3], true),
            new MeetingView(2, [$sheetView3, $sheetView], [$participantView4, $participantView], false),
            new MeetingView(3, [$sheetView2, $sheetView3], [$participantView3, $participantView4], true),
        ];

        $this->assertEquals($expected, $result);
    }

    public function testAllSheetParticipantsAssignedToMeeting()
    {
        $event = $this->prophesize(Event::class);

        $sheet1Participant1 = $this->prophesize(Participant::class);
        $sheet1Participant1->getId()->shouldBeCalled()->willReturn(101);

        $sheet1Participant2 = $this->prophesize(Participant::class);
        $sheet1Participant2->getId()->shouldBeCalled()->willReturn(102);

        $sheet2participant1 = $this->prophesize(Participant::class);
        $sheet2participant1->getId()->shouldBeCalled()->willReturn(103);

        $sheet3participant1 = $this->prophesize(Participant::class);
        $sheet3participant1->getId()->shouldBeCalled()->willReturn(104);

        $sheet4participant1 = $this->prophesize(Participant::class);
        $sheet4participant1->getId()->shouldBeCalled()->willReturn(106);

        $type1 = $this->prophesize(Type::class);
        $type1->areAllSheetParticipantsAssignedToMeeting()->shouldBeCalled()->willReturn(true);

        $type2 = $this->prophesize(Type::class);
        $type2->areAllSheetParticipantsAssignedToMeeting()->shouldBeCalled()->willReturn(false);

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet1->getId()->shouldBeCalled()->willReturn(1);
        $sheet1->getType()->shouldBeCalled()->willReturn($type1->reveal());
        $sheet1->hasLinkedSheets()->shouldBeCalled()->willReturn(false);
        $sheet1
            ->getParticipantsArray()
            ->shouldBeCalled()
            ->willReturn([$sheet1Participant1->reveal(), $sheet1Participant2->reveal()])
        ;

        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2->getId()->shouldBeCalled()->willReturn(2);
        $sheet2->getType()->shouldBeCalled()->willReturn($type1->reveal());
        $sheet2->hasLinkedSheets()->shouldBeCalled()->willReturn(true);
        $sheet2
            ->getLinkedSheetsParticipants()
            ->shouldBeCalled()
            ->willReturn([$sheet2participant1->reveal(), $sheet4participant1->reveal()])
        ;

        $sheet3 = $this->prophesize(Sheet::class);
        $sheet3->getId()->shouldBeCalled()->willReturn(3);
        $sheet3->getType()->shouldBeCalled()->willReturn($type2->reveal());

        $request1 = $this->prophesize(Request::class);
        $request1->getId()->shouldBeCalled()->willReturn(1001);
        $request1->getFromSheet()->shouldBeCalled()->willReturn($sheet1->reveal());
        $request1->getToSheet()->shouldBeCalled()->willReturn($sheet2->reveal());
        $request1->hasFromParticipants()->shouldBeCalled()->willReturn(true);
        $request1->getFromParticipantsArray()->shouldBeCalled()->willReturn([$sheet1Participant1->reveal()]);
        $request1->hasToParticipants()->shouldBeCalled()->willReturn(false);
        $request1->hasMeeting()->shouldBeCalled()->willReturn(false);
        $request1->getMeeting()->shouldBeCalled()->willReturn(null);

        $spot = $this->prophesize(Spot::class);
        $spot->getId()->shouldBeCalled()->willReturn(82);

        $meetingSlot = $this->prophesize(MeetingSlot::class);
        $meetingSlot->getId()->shouldBeCalled()->willReturn(71);

        $meetingForRequest2 = $this->prophesize(Meeting::class);
        $meetingForRequest2->getFromParticipantsArray()->shouldBeCalled()->willReturn([$sheet3participant1->reveal()]);
        $meetingForRequest2->getToParticipantsArray()->shouldBeCalled()->willReturn([$sheet1Participant1->reveal()]);
        $meetingForRequest2->getSlot()->shouldBeCalled()->willReturn($meetingSlot->reveal());
        $meetingForRequest2->getSpot()->shouldBeCalled()->willReturn($spot->reveal());
        $meetingForRequest2->isBlockedSlot()->shouldBeCalled()->willReturn(true);
        $meetingForRequest2->isBlockedSpot()->shouldBeCalled()->willReturn(false);

        $request2 = $this->prophesize(Request::class);
        $request2->getId()->shouldBeCalled()->willReturn(1002);
        $request2->hasFromParticipants()->shouldBeCalled()->willReturn(false);
        $request2->hasToParticipants()->shouldBeCalled()->willReturn(true);
        $request2->getFromSheet()->shouldBeCalled()->willReturn($sheet3->reveal());
        $request2->getToSheet()->shouldBeCalled()->willReturn($sheet1->reveal());
        $request2->hasMeeting()->shouldBeCalled()->willReturn(true);
        $request2->getMeeting()->shouldBeCalled()->willReturn($meetingForRequest2->reveal());

        $day = new Day(1, 12, 10, 2016);
        $slotView1 = new SlotView(71, 1, 10, 30, $day);
        $slotView2 = new SlotView(72, 2, 11, 0, $day);
        $slotView3 = new SlotView(73, 3, 11, 30, $day);
        $slotViews = [$slotView1, $slotView2, $slotView3];

        $typeView = new TypeView(1, 'Exhibitor');
        $sheetView1 = new SheetView(1, $typeView, 2, 2);
        $sheetView2 = new SheetView(2, $typeView, 2, 2);
        $sheetView3 = new SheetView(3, $typeView, 2, 2);
        $sheetView4 = new SheetView(4, $typeView, 2, 2);
        $sheetViews = [$sheetView1, $sheetView2, $sheetView3, $sheetView4];

        $spotView1 = new SpotView(81, false, 'Box A', 10, 2, [$sheetView1], 8, []);
        $spotView2 = new SpotView(82, false, 'Box B', 10, 2, [], 8, []);
        $spotViews = [$spotView1, $spotView2];

        $participantView1 = new ParticipantView(101, 11, 'Participant 1', $sheetView1, [], false);
        $participantView2 = new ParticipantView(102, 12, 'Participant 2', $sheetView1, [], true);
        $participantView3 = new ParticipantView(103, 13, 'Participant 3', $sheetView2, [], true);
        $participantView4 = new ParticipantView(104, 14, 'Participant 4', $sheetView3, [], false);
        $participantView5 = new ParticipantView(105, 15, 'Participant 5', $sheetView3, [], true);
        $participantView6 = new ParticipantView(106, 16, 'Participant 6', $sheetView4, [], true);
        $participantViews = [
            $participantView1,
            $participantView2,
            $participantView3,
            $participantView4,
            $participantView5,
            $participantView6,
        ];

        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $requestRepository
            ->getAllAcceptedByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn([$request1->reveal(), $request2->reveal()])
        ;

        $meetingView1 = new MeetingView(
            1001,
            [$sheetView1, $sheetView2],
            [$participantView1, $participantView2, $participantView3, $participantView6],
            true
        );

        $meetingView2 = new MeetingView(
            1002,
            [$sheetView3, $sheetView1],
            [$participantView4, $participantView1, $participantView2],
            true
        );
        $meetingView2->isBlockedSlot = true;
        $meetingView2->spot = $spotView2;
        $meetingView2->slot = $slotView1;
        $meetingView2->lockedSlot = $slotView1;

        $handler = new MeetingViewQueryHandler($requestRepository->reveal());
        $results = $handler->handle(
            new MeetingViewQuery(
                $event->reveal(),
                $sheetViews,
                $participantViews,
                $slotViews,
                $spotViews,
                'moving_allowed'
            )
        );
        $this->assertEquals([$meetingView2, $meetingView1], $results);
    }
}
