<?php

namespace Proximum\Vimeet\Tests\Application\Command\Event\Day;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Event\Day\Update;
use Proximum\Vimeet\Application\Command\Event\Day\UpdateHandler;
use Proximum\Vimeet\Application\Exception\Slot\SlotOutOfDayException;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Repository\Event\DayRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UpdateHandlerTest extends TestCase
{
    /**
     *  Add one day to the event
     */
    public function testHandleWithOneDay()
    {
        $event = EventFactory::createEvent();

        $starTime1 = new \DateTime('24-12-2016 08:00:00.000');
        $endTime1  = new \DateTime('24-12-2016 16:00:00.000');

        // Expected
        $expectedDay = new Day(
            $event,
            new \DateTime('24-12-2016 08:00:00.000'),
            new \DateTime('24-12-2016 16:00:00.000')
        );

        // Mock
        $dayRepository = $this->prophesize(DayRepositoryInterface::class);
        $dayRepository->removeFromEvent($event)->shouldBeCalled();
        $dayRepository->add($expectedDay)->shouldBeCalled();

        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $meetingSlotRepository->findByEvent($event)->shouldBeCalled()->willReturn([]);

        // Data
        $update  = new Update($event);
        $update->days[] = [
            'startTime' => $starTime1,
            'endTime'   => $endTime1,
        ];

        $handler = new UpdateHandler($dayRepository->reveal(), $meetingSlotRepository->reveal());
        $handler->handle($update);
    }

    /**
     *  Add three days to the event
     */
    public function testHandleWithThreeDays()
    {
        $event = EventFactory::createEvent();

        $starTime1 = new \DateTime('24-12-2016 08:00:00.000');
        $endTime1  = new \DateTime('24-12-2016 16:00:00.000');

        $starTime2 = new \DateTime('25-12-2016 10:00:00.000');
        $endTime2  = new \DateTime('25-12-2016 18:00:00.000');

        $starTime3 = new \DateTime('26-12-2016 12:30:00.000');
        $endTime3  = new \DateTime('26-12-2016 20:45:00.000');

        // Expected
        $expectedDay1 = new Day(
            $event,
            new \DateTime('24-12-2016 08:00:00.000'),
            new \DateTime('24-12-2016 16:00:00.000')
        );
        $expectedDay2 = new Day(
            $event,
            new \DateTime('25-12-2016 10:00:00.000'),
            new \DateTime('25-12-2016 18:00:00.000')
        );
        $expectedDay3 = new Day(
            $event,
            new \DateTime('26-12-2016 12:30:00.000'),
            new \DateTime('26-12-2016 20:45:00.000')
        );

        // Mock
        $dayRepository = $this->prophesize(DayRepositoryInterface::class);
        $dayRepository->removeFromEvent($event)->shouldBeCalled();
        $dayRepository->add($expectedDay1)->shouldBeCalled();
        $dayRepository->add($expectedDay2)->shouldBeCalled();
        $dayRepository->add($expectedDay3)->shouldBeCalled();

        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $meetingSlotRepository->findByEvent($event)->shouldBeCalled()->willReturn([]);

        // Data
        $update  = new Update($event);
        $update->days[] = [
            'startTime' => $starTime1,
            'endTime'   => $endTime1,
        ];
        $update->days[] = [
            'startTime' => $starTime2,
            'endTime'   => $endTime2,
        ];
        $update->days[] = [
            'startTime' => $starTime3,
            'endTime'   => $endTime3,
        ];

        $handler = new UpdateHandler($dayRepository->reveal(), $meetingSlotRepository->reveal());
        $handler->handle($update);
    }

    public function testHandleWithThreeDaysWithSlotOutOfDay()
    {
        $this->expectException(SlotOutOfDayException::class);
        $event = EventFactory::createEvent();

        $starTime1 = new \DateTime('24-12-2016 08:00:00.000');
        $endTime1  = new \DateTime('24-12-2016 16:00:00.000');

        $starTime2 = new \DateTime('25-12-2016 10:00:00.000');
        $endTime2  = new \DateTime('25-12-2016 18:00:00.000');

        $starTime3 = new \DateTime('26-12-2016 12:30:00.000');
        $endTime3  = new \DateTime('26-12-2016 20:45:00.000');

        // Expected
        $expectedDay1 = new Day(
            $event,
            new \DateTime('24-12-2016 08:00:00.000'),
            new \DateTime('24-12-2016 16:00:00.000')
        );
        $expectedDay2 = new Day(
            $event,
            new \DateTime('25-12-2016 10:00:00.000'),
            new \DateTime('25-12-2016 18:00:00.000')
        );
        $expectedDay3 = new Day(
            $event,
            new \DateTime('26-12-2016 12:30:00.000'),
            new \DateTime('26-12-2016 20:45:00.000')
        );

        // Mock
        $dayRepository = $this->prophesize(DayRepositoryInterface::class);
        $dayRepository->removeFromEvent($event)->shouldNotBeCalled();
        $dayRepository->add($expectedDay1)->shouldNotBeCalled();
        $dayRepository->add($expectedDay2)->shouldNotBeCalled();
        $dayRepository->add($expectedDay3)->shouldNotBeCalled();

        $slot1 = $this->prophesize(MeetingSlot::class);
        $slot2 = $this->prophesize(MeetingSlot::class);
        $slot3 = $this->prophesize(MeetingSlot::class);
        $slot1->getBegin()->willReturn($starTime1);
        $slot1->getEnd()->willReturn($starTime1);
        $slot2->getBegin()->willReturn(new \DateTime('25-12-2016 18:00:00.000'));
        $slot2->getEnd()->willReturn(new \DateTime('25-12-2016 18:30:00.000'));
        $slot3->getBegin()->willReturn($starTime3);
        $slot3->getEnd()->willReturn($endTime3);

        $slots = [$slot1->reveal(), $slot2->reveal(), $slot3->reveal()];

        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $meetingSlotRepository->findByEvent($event)->shouldBeCalled()->willReturn($slots);

        // Data
        $update  = new Update($event);
        $update->days[] = [
            'startTime' => $starTime1,
            'endTime'   => $endTime1,
        ];
        $update->days[] = [
            'startTime' => $starTime2,
            'endTime'   => $endTime2,
        ];
        $update->days[] = [
            'startTime' => $starTime3,
            'endTime'   => $endTime3,
        ];

        $handler = new UpdateHandler($dayRepository->reveal(), $meetingSlotRepository->reveal());
        $handler->handle($update);
    }

    /**
     *  Remove all the days of the event
     */
    public function testHandleWithoutDays()
    {
        $event = EventFactory::createEvent();

        // Unexpected
        $unExpectedDay = new Day(
            $event,
            new \DateTime('24-12-2016 08:00:00.000'),
            new \DateTime('24-12-2016 16:00:00.000')
        );

        // Mock
        $dayRepository = $this->prophesize(DayRepositoryInterface::class);
        $dayRepository->removeFromEvent($event)->shouldBeCalled();
        $dayRepository->add($unExpectedDay)->shouldNotBeCalled();

        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $meetingSlotRepository->findByEvent($event)->shouldBeCalled()->willReturn([]);

        // Data
        $update = new Update($event);

        $handler = new UpdateHandler($dayRepository->reveal(), $meetingSlotRepository->reveal());
        $handler->handle($update);
    }
}
