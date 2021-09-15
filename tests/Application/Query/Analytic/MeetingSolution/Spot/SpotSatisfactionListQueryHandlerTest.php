<?php

namespace Proximum\Vimeet\Tests\Application\Query\Analytic\MeetingSolution\Spot;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\Spot\SpotSatisfactionListQuery;
use Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\Spot\SpotSatisfactionListQueryHandler;
use Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\Spot\SpotSatisfactionViewQuery;
use Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\Spot\SpotSatisfactionViewQueryHandler;
use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Spot\SpotSatisfactionView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class SpotSatisfactionListQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $slot1 = $this->prophesize(MeetingSlot::class);
        $slot2 = $this->prophesize(MeetingSlot::class);
        $slots = [$slot1->reveal(), $slot2->reveal()];
        $spot1 = $this->prophesize(Spot::class);
        $spot2 = $this->prophesize(Spot::class);
        $spot1->getId()->willReturn(1);
        $spot2->getId()->willReturn(2);
        $spots = [$spot1->reveal(), $spot2->reveal()];

        // Mock
        $spotRepository = $this->prophesize(SpotRepositoryInterface::class);
        $spotRepository->getActiveByEvent($event->reveal())->shouldBeCalled()->willReturn($spots);

        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository
            ->countMeetingsBySpots($spots)
            ->shouldBeCalled()
            ->willReturn([
                1 => ['countMeetings' => 12],
            ])
        ;

        $spotSatisfactionView1 = $this->prophesize(SpotSatisfactionView::class);
        $spotSatisfactionView2 = $this->prophesize(SpotSatisfactionView::class);
        $spotSatisfactionViewQueryHandler = $this->prophesize(SpotSatisfactionViewQueryHandler::class);
        $spotSatisfactionViewQueryHandler
            ->handle(new SpotSatisfactionViewQuery($spot1->reveal(), 12, 2))
            ->shouldBeCalled()
            ->willReturn($spotSatisfactionView1->reveal());

        $spotSatisfactionViewQueryHandler
            ->handle(new SpotSatisfactionViewQuery($spot2->reveal(), 0, 2))
            ->shouldBeCalled()
            ->willReturn($spotSatisfactionView2->reveal());

        $handler = new SpotSatisfactionListQueryHandler(
            $spotRepository->reveal(),
            $meetingRepository->reveal(),
            $spotSatisfactionViewQueryHandler->reveal()
        );
        $result = $handler->handle(new SpotSatisfactionListQuery($event->reveal(), $slots));

        $expected = [$spotSatisfactionView1->reveal(), $spotSatisfactionView2->reveal()];

        $this->assertEquals($expected, $result);
    }
}
