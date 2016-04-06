<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Spot;

use Proximum\Vimeet\Application\Command\Spot\Create;
use Proximum\Vimeet\Application\Command\Spot\CreateHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class CreateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        //Context
        $event = new Event();

        //Command
        $command = new Create($event);
        $command->active = true;
        $command->meetingCapacity = 4;
        $command->reference = 'test013';
        $command->size = 2;
        $command->seatCapacity = 5;

        //Expected
        $expectedSpot = new Spot('test013', $event, 2, 4, 5, true);

        $spotRepository = $this->prophesize(SpotRepositoryInterface::class);
        $spotRepository->add($expectedSpot)->shouldBeCalled();
        $spotRepository->findByReference($event, 'test013')->shouldBeCalled()->willReturn(null);

        //Handler
        $handle = new CreateHandler($spotRepository->reveal());
        $handle->handle($command);
    }
}
