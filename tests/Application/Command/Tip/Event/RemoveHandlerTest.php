<?php

namespace Application\Command\Tip\Event;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Command\Tip\Event\Remove;
use Proximum\Vimeet\Application\Command\Tip\Event\RemoveHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Tip\RemovedEvent;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class RemoveHandlerTest extends TestCase
{
    public function testHandle()
    {
        $tipRepository = $this->prophesize(TipRepositoryInterface::class);
        $tip = $this->prophesize(Tip::class);
        $event = $this->prophesize(Event::class);
        $tip->getEvent()->willReturn($event->reveal());

        $tipRepository->removeTip($tip->reveal())->shouldBeCalled();
        $eventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
        $eventDispatcher->dispatch(Events::TIP_REMOVED_FROM_EVENT, new RemovedEvent($event->reveal()))->shouldBeCalled();

        $remove  = new Remove($tip->reveal());
        $handler = new RemoveHandler(
            $tipRepository->reveal(),
            $eventDispatcher->reveal()
        );

        $handler->handle($remove);
    }
}
