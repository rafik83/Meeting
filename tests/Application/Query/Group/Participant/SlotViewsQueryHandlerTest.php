<?php

namespace Proximum\Vimeet\Tests\Application\Query\Group\Participant;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Group\Participant\SlotViewsQuery;
use Proximum\Vimeet\Application\Query\Group\Participant\SlotViewsQueryHandler;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class SlotViewsQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $day   = new Day($event, new \DateTime(), new \DateTime());
        $meetingSlot1 = new MeetingSlot($event, new \DateTime(), new \DateTime());
        $meetingSlot2 = new MeetingSlot($event, new \DateTime(), new \DateTime());
        $meetingSlot3 = new MeetingSlot($event, new \DateTime(), new \DateTime());

        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $meetingSlotRepository
            ->findByEventAndDay($event, $day)
            ->shouldBeCalled()
            ->willReturn([$meetingSlot1, $meetingSlot2, $meetingSlot3])
        ;

        $handler = new SlotViewsQueryHandler($meetingSlotRepository->reveal());
        $result  = $handler->handle(new SlotViewsQuery($day));

        $this->assertCount(3, $result);
    }
}
