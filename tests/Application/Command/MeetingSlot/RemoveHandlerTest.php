<?php

namespace Proximum\Vimeet\Tests\Application\Command\MeetingSlot;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Command\MeetingSlot\Remove;
use Proximum\Vimeet\Application\Command\MeetingSlot\RemoveHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Slot\DeletedEvent;
use Proximum\Vimeet\Application\Exception\Slot\IsNotAllowedToRemoveSlotException;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class RemoveHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $begin = new \DateTime();
        $end   = new \DateTime();

        $meetingSlot = new MeetingSlot($event, $begin, $end, false);

        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository->hasMeetingOnSlot($meetingSlot)->shouldBeCalled()->willReturn(false);
        $meetingSlotRepository->remove($meetingSlot)->shouldBeCalled();

        $eventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
        $eventDispatcher->dispatch(Events::SLOT_DELETED, new DeletedEvent($event))->shouldBeCalled();

        $handler = new RemoveHandler(
            $meetingSlotRepository->reveal(),
            $meetingRepository->reveal(),
            $eventDispatcher->reveal()
        );
        $handler->handle(new Remove($meetingSlot));
    }

    public function testIsNotAllowedToRemoveSlotException()
    {
        $this->expectException(IsNotAllowedToRemoveSlotException::class);

        $event = EventFactory::createEvent();
        $begin = new \DateTime();
        $end   = new \DateTime();

        $meetingSlot = new MeetingSlot($event, $begin, $end, false);

        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository->hasMeetingOnSlot($meetingSlot)->shouldBeCalled()->willReturn(true);

        $eventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
        $eventDispatcher->dispatch(Events::SLOT_DELETED, new DeletedEvent($event))->shouldNotBeCalled();

        $handler = new RemoveHandler(
            $meetingSlotRepository->reveal(),
            $meetingRepository->reveal(),
            $eventDispatcher->reveal()
        );
        $handler->handle(new Remove($meetingSlot));
    }
}
