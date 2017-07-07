<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Event;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Event\UnArchive;
use Proximum\Vimeet\Application\Command\Event\UnArchiveHandler;
use Proximum\Vimeet\Domain\Exception\Event\EventNotArchivedException;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UnArchiveHandlerTest extends TestCase
{
    public function testHandleException()
    {
        $this->expectException(EventNotArchivedException::class);

        // context
        $event = EventFactory::createEvent('title');

        // Mock
        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository->set(Argument::any())->shouldNotBeCalled();

        // handler
        $unArchiveHandler = new UnArchiveHandler($eventRepository->reveal());
        $unArchiveHandler->handle(new UnArchive($event));
    }

    public function testHandle()
    {
        // context
        $event = EventFactory::createEvent('title');
        $event->archive();

        // Expected
        $expectedEvent = EventFactory::createEvent('title');

        // Mock
        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository->set($expectedEvent)->shouldBeCalled();

        // handler
        $unArchiveHandler = new UnArchiveHandler($eventRepository->reveal());
        $unArchiveHandler->handle(new UnArchive($event));
    }
}
