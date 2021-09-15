<?php

namespace Proximum\Vimeet\Tests\Application\Command\Event\Index;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Command\Event\Index\IndexHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class IndexHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $jobQueue = $this->prophesize(JobQueueInterface::class);

        $event1 = $this->prophesize(Event::class);
        $event2 = $this->prophesize(Event::class);
        $eventRepository->getEventsOrderByIdDesc()->shouldBeCalled()->willReturn([$event1->reveal(), $event2->reveal()]);
        $jobQueue->indexSheetsByEvent($event1->reveal(), false)->shouldBeCalled();
        $jobQueue->indexSheetsByEvent($event2->reveal(), false)->shouldBeCalled();

        $handler = new IndexHandler($eventRepository->reveal(), $jobQueue->reveal());
        $handler->handle();
    }
}
