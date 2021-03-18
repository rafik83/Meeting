<?php

namespace Proximum\Vimeet\Tests\Application\Query\Schedule;

use DateTime;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Schedule\SlotViewQuery;
use Proximum\Vimeet\Application\Query\Schedule\SlotViewQueryHandler;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class SlotViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $date  = new Datetime();
        $slot1 = $this->prophesize(MeetingSlot::class);
        $slot2 = $this->prophesize(MeetingSlot::class);

        $slot1->getBegin()->shouldBeCalled()->willReturn($date);
        $slot1->getEnd()->shouldBeCalled()->willReturn($date);
        $slot1->duration()->shouldBeCalled()->willReturn('30 min');
        $slot1->isLocked()->shouldBeCalled()->willReturn(true);
        $slot1->getId()->shouldBeCalled()->willReturn(1);

        $slot2->getBegin()->shouldBeCalled()->willReturn($date);
        $slot2->getEnd()->shouldBeCalled()->willReturn($date);
        $slot2->duration()->shouldBeCalled()->willReturn('30 min');
        $slot2->isLocked()->shouldBeCalled()->willReturn(true);
        $slot2->getId()->shouldBeCalled()->willReturn(2);

        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $meetingRepository     = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingSlotRepository->findWithAtLeastOneMeetingByEvent($event)->shouldBeCalled()->willReturn(['1' => 1]);
        $meetingSlotRepository->findByEvent($event)->shouldBeCalled()->willReturn([$slot1->reveal(), $slot2->reveal()]);

        $handler = new SlotViewQueryHandler($meetingSlotRepository->reveal(), $meetingRepository->reveal());
        $result  = $handler->handle(new SlotViewQuery($event));

        $this->assertCount(2, $result);
        $this->assertTrue($result[0]->disabled);
        $this->assertFalse($result[1]->disabled);
    }
}
