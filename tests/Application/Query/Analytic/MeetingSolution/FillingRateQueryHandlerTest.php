<?php

namespace Proximum\Vimeet\Tests\Application\Query\Analytic\MeetingSolution;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\FillingRateQuery;
use Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\FillingRateQueryHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\SpotUnavailability;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;

class FillingRateQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $slot1 = $this->prophesize(MeetingSlot::class);
        $slot2 = $this->prophesize(MeetingSlot::class);
        $spot1 = $this->prophesize(Spot::class);
        $spot2 = $this->prophesize(Spot::class);
        $spot3 = $this->prophesize(Spot::class);
        $spot4 = $this->prophesize(Spot::class);
        $spot1->getId()->willReturn(1);
        $spot2->getId()->willReturn(2);
        $spot3->getId()->willReturn(3);
        $spot4->getId()->willReturn(4);
        $spot1->getMeetingCapacity()->willReturn(2);
        $spot2->getMeetingCapacity()->willReturn(4);
        $spot3->getMeetingCapacity()->willReturn(2);
        $spot4->getMeetingCapacity()->willReturn(2);
        $slot1->getId()->willReturn(11);
        $slot2->getId()->willReturn(12);
        $spotUnavailability1 = $this->prophesize(SpotUnavailability::class);
        $spotUnavailability1->getSlot()->willReturn($slot1->reveal());
        $spotUnavailability2 = $this->prophesize(SpotUnavailability::class);
        $spotUnavailability2->getSlot()->willReturn($slot2->reveal());
        $spotUnavailability3 = $this->prophesize(SpotUnavailability::class);
        $spotUnavailability3->getSlot()->willReturn($slot2->reveal());
        $spotUnavailability4 = $this->prophesize(SpotUnavailability::class);
        $spotUnavailability4->getSlot()->willReturn($slot1->reveal());
        $spot1->getSpotUnavailabilities()->willReturn([]);
        $spot2->getSpotUnavailabilities()->willReturn([$spotUnavailability1->reveal(), $spotUnavailability2->reveal()]);
        $spot3->getSpotUnavailabilities()->willReturn([$spotUnavailability3->reveal()]);
        $spot4->getSpotUnavailabilities()->willReturn([$spotUnavailability4->reveal()]);
        $slots = [$slot1->reveal(), $slot2->reveal()];
        $spots = [$spot1->reveal(), $spot2->reveal(), $spot3->reveal(), $spot4->reveal()];

        // Mock
        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository->countMeetingForSpots($spots)->shouldBeCalled()->willReturn(5);

        $handler = new FillingRateQueryHandler($meetingRepository->reveal());
        $result = $handler->handle(new FillingRateQuery($event->reveal(), $slots, $spots));

        $this->assertEquals(62, $result);
    }
}
