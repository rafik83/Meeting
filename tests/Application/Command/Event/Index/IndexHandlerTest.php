<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Event\Index;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Command\Event\Index\IndexHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class IndexHandlerTest extends TestCase
{
    public function testHandle()
    {
        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $jobQueue = $this->prophesize(JobQueueInterface::class);

        $event1 = $this->prophesize(Event::class);
        $event2 = $this->prophesize(Event::class);
        $eventRepository->getEventsOrderByIdDesc()->shouldBeCalled()->willReturn([$event1->reveal(), $event2->reveal()]);
        $jobQueue->indexSheetsByEvent($event1->reveal())->shouldBeCalled();
        $jobQueue->indexSheetsByEvent($event2->reveal())->shouldBeCalled();

        $handler = new IndexHandler($eventRepository->reveal(), $jobQueue->reveal());
        $handler->handle();
    }
}
