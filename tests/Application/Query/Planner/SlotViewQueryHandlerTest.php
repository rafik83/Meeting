<?php

namespace Proximum\Vimeet\Tests\Application\Query\Planner;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Exception\Planner\SlotNotConfiguredException;
use Proximum\Vimeet\Application\Query\Planner\SlotViewQuery;
use Proximum\Vimeet\Application\Query\Planner\SlotViewQueryHandler;
use Proximum\Vimeet\Application\View\Planner\Day;
use Proximum\Vimeet\Application\View\Planner\SlotView;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class SlotViewQueryHandlerTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $slotRepository;

    public function setUp()
    {
        $this->slotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
    }

    /**
     * @throws SlotNotConfiguredException
     */
    public function testHandleNoDayException()
    {
        $this->expectException(SlotNotConfiguredException::class);
        $event = EventFactory::createEvent();

        $slot = new MeetingSlot($event, new \DateTime(), new \DateTime(), true);
        $this->slotRepository->getAvailableSlotByEvent($event)->shouldBeCalled()->willReturn([$slot]);

        $slotViewQueryHandler = new SlotViewQueryHandler($this->slotRepository->reveal());
        $slotViewQueryHandler->handle(new SlotViewQuery($event, []));
    }

    /**
     * @throws SlotNotConfiguredException
     */
    public function testHandleSlotOutOfDayException()
    {
        $this->expectException(SlotNotConfiguredException::class);

        $event = EventFactory::createEvent();
        $day   = new Day(1, 12, 10, 2016);
        $slot  = new MeetingSlot($event, new \DateTime('2016-10-14 10:00:00.000'), new \DateTime('2016-10-14 11:00:00.000'), true);
        $this->slotRepository->getAvailableSlotByEvent($event)->shouldBeCalled()->willReturn([$slot]);

        $slotViewQueryHandler = new SlotViewQueryHandler($this->slotRepository->reveal());
        $slotViewQueryHandler->handle(new SlotViewQuery($event, [$day]));
    }

    public function testHandle()
    {
        // Data
        $event = EventFactory::createEvent();
        $day   = new Day(1, 12, 10, 2016);
        $day2  = new Day(1, 13, 10, 2016);
        $slot1 = new MeetingSlot(
            $event,
            new \DateTime('2016-10-12 10:00:00.000'),
            new \DateTime('2016-10-12 10:30:00.000'),
            true
        );
        $slot2 = new MeetingSlot($event,
            new \DateTime('2016-10-12 11:00:00.000'),
            new \DateTime('2016-10-12 11:30:00.000'),
            true
        );
        $slot3 = new MeetingSlot($event,
            new \DateTime('2016-10-13 10:30:00.000'),
            new \DateTime('2016-10-13 11:00:00.000'),
            true
        );

        // Reflection
        $reflection = new \ReflectionClass(MeetingSlot::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($slot1, 1);
        $property->setValue($slot2, 2);
        $property->setValue($slot3, 3);
        $property->setAccessible(false);

        // Mock
        $this->slotRepository->getAvailableSlotByEvent($event)->shouldBeCalled()->willReturn([$slot1, $slot2, $slot3]);

        // Handler
        $slotViewQueryHandler = new SlotViewQueryHandler($this->slotRepository->reveal());
        $result = $slotViewQueryHandler->handle(new SlotViewQuery($event, [$day, $day2]));

        // Expected
        $expected = [
            new SlotView(1, 0, 10, 0, $day),
            new SlotView(2, 1, 11, 0, $day),
            new SlotView(3, 2, 10, 30, $day2),
        ];

        // Assertion
        $this->assertEquals($expected, $result);
    }
}
