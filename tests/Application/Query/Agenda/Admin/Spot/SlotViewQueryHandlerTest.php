<?php

namespace Proximum\Vimeet\Tests\Application\Query\Agenda\Admin\Spot;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Agenda\Admin\Spot\MeetingSlotViewQueryHandler;
use Proximum\Vimeet\Application\Query\Agenda\Admin\Spot\SlotViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\Spot\SlotViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\Slot\AbstractSlotView;
use Proximum\Vimeet\Application\View\Agenda\Slot\SpotSlotView;
use Proximum\Vimeet\Domain\Meeting\Slot\SlotAvailability;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\SpotUnavailability;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotUnavailabilityRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SlotFactory;
use Proximum\Vimeet\Tests\Factory\SpotFactory;

class SlotViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event     = EventFactory::createEvent();
        $spot      = SpotFactory::create($event);
        $startTime = new \DateTime();
        $endTime   = new \DateTime();
        $day       = new Day($event, $startTime, $endTime);
        $locale    = 'fr';

        $slot               = SlotFactory::createSlot(1, $event);
        $unavailableSlot    = SlotFactory::createSlot(2, $event);
        $spotUnavailability = new SpotUnavailability($unavailableSlot, $spot);

        // Mock
        $meetingRepository            = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingSlotRepository        = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $spotUnavailabilityRepository = $this->prophesize(SpotUnavailabilityRepositoryInterface::class);
        $meetingSlotViewQueryHandler  = $this->prophesize(MeetingSlotViewQueryHandler::class);

        $spotUnavailabilityRepository->findBySpot($spot)->shouldBeCalled()->willReturn([
            $spotUnavailability,
        ]);

        $meetingSlotRepository->findByEventAndDay($event, $day)->shouldBeCalled()->willReturn([
            $slot,
            $unavailableSlot,
        ]);

        $meetingRepository->findBySpotWithSheets($spot)->shouldBeCalled()->willReturn([]);

        // Expected
        $expectedSpotSlotViews = [
            new SpotSlotView($slot, SlotAvailability::SLOT_AVAILABLE, []),
            new SpotSlotView($unavailableSlot, SlotAvailability::UNAVAILABILITY, []),
        ];

        $query = new SlotViewQuery($event, $day, $spot, $locale);

        $handler = new SlotViewQueryHandler(
            $meetingRepository->reveal(),
            $meetingSlotRepository->reveal(),
            $spotUnavailabilityRepository->reveal(),
            $meetingSlotViewQueryHandler->reveal()
        );

        $spotSlotViews = $handler->handle($query);

        $this->assertEquals($expectedSpotSlotViews, $spotSlotViews);
        $this->assertCount(2, $spotSlotViews);
        $this->assertInstanceOf(AbstractSlotView::class, $spotSlotViews[0]);
    }
}
