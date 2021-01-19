<?php

namespace Proximum\Vimeet\Tests\Application\Command\MeetingSlot;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Command\MeetingSlot\Unlock;
use Proximum\Vimeet\Application\Command\MeetingSlot\UnlockHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Slot\ToggleLockedEvent;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UnlockHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $begin = new \DateTime();
        $end   = new \DateTime();

        $lockedMeetingSlot   = new MeetingSlot($event, $begin, $end, true);
        $unlockedMeetingSlot = new MeetingSlot($event, $begin, $end, false);

        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $meetingSlotRepository->set($unlockedMeetingSlot)->shouldBeCalled();

        $eventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
        $eventDispatcher->dispatch(Events::SLOT_TOGGLE_LOCKED, new ToggleLockedEvent($event))->shouldBeCalled();

        $handler = new UnlockHandler($meetingSlotRepository->reveal(), $eventDispatcher->reveal());
        $handler->handle(new Unlock($lockedMeetingSlot));
    }
}
