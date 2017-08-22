<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Spot\Action;

use Proximum\Vimeet\Application\Command\Spot\Action\Visio;
use Proximum\Vimeet\Application\Command\Spot\Action\VisioHandler;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use PHPUnit\Framework\TestCase;

class VisioHandlerTest extends TestCase
{
    public function testHandle()
    {
        // Data
        $event = EventFactory::createEvent();
        $spot  = new Spot('ref', $event, 10, 3, 3, true, 10, false);

        // Expected
        $expectedSpot = new Spot('ref', $event, 10, 3, 3, true, 10, true);

        // Mock
        $spotRepository = $this->prophesize(SpotRepositoryInterface::class);
        $spotRepository->set($expectedSpot)->shouldBeCalled();

        // Handler
        $handler = new VisioHandler($spotRepository->reveal());
        $handler->handle(new Visio($spot));
    }
}
