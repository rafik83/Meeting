<?php

namespace Proximum\Vimeet\Tests\Application\Command\MeetingSlot;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Command\MeetingSlot\Lock;
use Proximum\Vimeet\Application\Command\MeetingSlot\LockHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Slot\ToggleLockedEvent;
use Proximum\Vimeet\Application\Exception\Slot\IsNotAllowedToLockSlotException;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class LockHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $begin = new \DateTime();
        $end   = new \DateTime();

        $unlockedMeetingSlot = new MeetingSlot($event, $begin, $end, false);
        $lockedMeetingSlot   = new MeetingSlot($event, $begin, $end, true);

        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $meetingSlotRepository->set($lockedMeetingSlot)->shouldBeCalled();
        $meetingSlotRepository->findWithAtLeastOneMeetingByEvent($event)->shouldBeCalled();

        $eventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
        $eventDispatcher->dispatch(Events::SLOT_TOGGLE_LOCKED, new ToggleLockedEvent($event))->shouldBeCalled();

        $handler = new LockHandler($meetingSlotRepository->reveal(), $eventDispatcher->reveal());
        $handler->handle(new Lock($unlockedMeetingSlot));
    }

    public function testIsNotAllowedToLockSlotException()
    {
        $this->expectException(IsNotAllowedToLockSlotException::class);

        $event = EventFactory::createEvent();

        $meetingSlot = $this->prophesize(MeetingSlot::class);
        $meetingSlot->getId()->shouldBeCalled()->willReturn(1);
        $meetingSlot->getEvent()->shouldBeCalled()->willReturn($event);

        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $meetingSlotRepository->findWithAtLeastOneMeetingByEvent($event)->shouldBeCalled()->willReturn(['1' => 'meeting']);

        $eventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
        $eventDispatcher->dispatch(Events::SLOT_TOGGLE_LOCKED, new ToggleLockedEvent($event))->shouldNotBeCalled();

        $handler = new LockHandler($meetingSlotRepository->reveal(), $eventDispatcher->reveal());
        $handler->handle(new Lock($meetingSlot->reveal()));
    }
}
