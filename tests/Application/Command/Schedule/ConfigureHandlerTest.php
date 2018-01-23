<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Schedule;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Schedule\Configure;
use Proximum\Vimeet\Application\Command\Schedule\ConfigureHandler;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class ConfigureHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $expectedEvent = EventFactory::createEvent();
        $expectedEvent->getConfiguration()->setScheduleScale(12);

        $command = new Configure($event);
        $command->scale = 12;

        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $eventRepository->set($expectedEvent)->shouldBeCalled();

        $handler = new ConfigureHandler($eventRepository->reveal());
        $handler->handle($command);
    }
}
