<?php

namespace Proximum\Vimeet\Tests\Application\Query\Planner;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Planner\DayViewQuery;
use Proximum\Vimeet\Application\Query\Planner\DayViewQueryHandler;
use Proximum\Vimeet\Application\Query\Planner\MeetingViewQuery;
use Proximum\Vimeet\Application\Query\Planner\MeetingViewQueryHandler;
use Proximum\Vimeet\Application\Query\Planner\ParticipantViewQuery;
use Proximum\Vimeet\Application\Query\Planner\ParticipantViewQueryHandler;
use Proximum\Vimeet\Application\Query\Planner\PlannerViewQuery;
use Proximum\Vimeet\Application\Query\Planner\PlannerViewQueryHandler;
use Proximum\Vimeet\Application\Query\Planner\SheetViewQuery;
use Proximum\Vimeet\Application\Query\Planner\SheetViewQueryHandler;
use Proximum\Vimeet\Application\Query\Planner\SlotViewQuery;
use Proximum\Vimeet\Application\Query\Planner\SlotViewQueryHandler;
use Proximum\Vimeet\Application\Query\Planner\SpotViewQuery;
use Proximum\Vimeet\Application\Query\Planner\SpotViewQueryHandler;
use Proximum\Vimeet\Application\Query\Planner\TypePriorityViewQuery;
use Proximum\Vimeet\Application\Query\Planner\TypePriorityViewQueryHandler;
use Proximum\Vimeet\Application\Query\Planner\TypeViewQuery;
use Proximum\Vimeet\Application\Query\Planner\TypeViewQueryHandler;
use Proximum\Vimeet\Application\View\Planner\Day;
use Proximum\Vimeet\Application\View\Planner\MeetingView;
use Proximum\Vimeet\Application\View\Planner\ParticipantView;
use Proximum\Vimeet\Application\View\Planner\PlannerView;
use Proximum\Vimeet\Application\View\Planner\SheetView;
use Proximum\Vimeet\Application\View\Planner\SlotView;
use Proximum\Vimeet\Application\View\Planner\SpotView;
use Proximum\Vimeet\Application\View\Planner\TypePriorityView;
use Proximum\Vimeet\Application\View\Planner\TypeView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class PlannerViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $day   = new Event\Day($event, new \DateTime(), new \DateTime());
        $event->setDays([$day]);

        $dayView          = new Day(1, 12, 10, 2016);
        $slotView         = new SlotView(1, 1, 10, 0, $dayView);
        $typeView         = new TypeView(1, 'type');
        $typePriorityView = new TypePriorityView($typeView, $typeView, 1);
        $sheetView        = new SheetView(1, $typeView, 2, 5);
        $participantView  = new ParticipantView(1, 1, 'fullName', $sheetView, [$slotView]);
        $meetingView      = new MeetingView(1, [$sheetView], [$participantView]);
        $spotView         = new SpotView(1, true, 'ref', 2, 3, [$sheetView], 1);

        // Mock
        $dayViewQueryHandler          = $this->prophesize(DayViewQueryHandler::class);
        $slotViewQueryHandler         = $this->prophesize(SlotViewQueryHandler::class);
        $typeViewQueryHandler         = $this->prophesize(TypeViewQueryHandler::class);
        $typePriorityViewQueryHandler = $this->prophesize(TypePriorityViewQueryHandler::class);
        $sheetViewQueryHandler        = $this->prophesize(SheetViewQueryHandler::class);
        $participantViewQueryHandler  = $this->prophesize(ParticipantViewQueryHandler::class);
        $meetingViewQueryHandler      = $this->prophesize(MeetingViewQueryHandler::class);
        $spotViewQueryHandler         = $this->prophesize(SpotViewQueryHandler::class);

        $dayViewQueryHandler->handle(new DayViewQuery([$day]))->shouldBeCalled()->willReturn([$dayView]);
        $slotViewQueryHandler->handle(new SlotViewQuery($event, [$dayView]))->shouldBeCalled()->willReturn([$slotView]);
        $typeViewQueryHandler->handle(new TypeViewQuery($event, 'fr'))->shouldBeCalled()->willReturn([$typeView]);
        $typePriorityViewQueryHandler
            ->handle(new TypePriorityViewQuery($event, [$typeView]))
            ->shouldBeCalled()
            ->willReturn([$typePriorityView]);
        $sheetViewQueryHandler
            ->handle(new SheetViewQuery($event, [$typeView], 'moving_allowed'))
            ->shouldBeCalled()
            ->willReturn([$sheetView]);
        $participantViewQueryHandler
            ->handle(new ParticipantViewQuery($event, [$sheetView], [$slotView]))
            ->shouldBeCalled()
            ->willReturn([$participantView]);
        $meetingViewQueryHandler
            ->handle(new MeetingViewQuery($event, [$sheetView], [$participantView], [$slotView], [$spotView], 'moving_allowed'))
            ->shouldBeCalled()
            ->willReturn([$meetingView]);
        $spotViewQueryHandler
            ->handle(new SpotViewQuery($event, [$sheetView], [$slotView]))
            ->shouldBeCalled()
            ->willReturn([$spotView]);

        $plannerViewQueryHandler = new PlannerViewQueryHandler(
            $dayViewQueryHandler->reveal(),
            $slotViewQueryHandler->reveal(),
            $typeViewQueryHandler->reveal(),
            $typePriorityViewQueryHandler->reveal(),
            $sheetViewQueryHandler->reveal(),
            $participantViewQueryHandler->reveal(),
            $meetingViewQueryHandler->reveal(),
            $spotViewQueryHandler->reveal()
        );

        $result = $plannerViewQueryHandler->handle(new PlannerViewQuery($event, 'fr', 'moving_allowed'));

        // Expected
        $expected = new PlannerView(
            [$dayView],
            [$slotView],
            [$typeView],
            [$typePriorityView],
            [$sheetView],
            [$participantView],
            [$meetingView],
            [$spotView]
        );

        $this->assertEquals($expected, $result);
    }
}
