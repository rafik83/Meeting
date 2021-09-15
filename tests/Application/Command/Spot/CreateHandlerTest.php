<?php

namespace Proximum\Vimeet\Tests\Application\Command\Spot;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Spot\Create;
use Proximum\Vimeet\Application\Command\Spot\CreateHandler;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class CreateHandlerTest extends TestCase
{
    public function testHandle()
    {
        //Context
        $event = EventFactory::createEvent();

        //Command
        $command = new Create($event);
        $command->active = true;
        $command->meetingCapacity = 4;
        $command->reference = 'test013';
        $command->size = 2;
        $command->seatCapacity = 5;
        $command->visio = true;
        $command->priority = 8;

        //Expected
        $expectedSpot = new Spot('test013', $event, 2, 4, 5, true, 8, true);

        $spotRepository = $this->prophesize(SpotRepositoryInterface::class);
        $spotRepository->add($expectedSpot)->shouldBeCalled();
        $spotRepository->findByReference($event, 'test013')->shouldBeCalled()->willReturn(null);

        //Handler
        $handle = new CreateHandler($spotRepository->reveal());
        $handle->handle($command);
    }
}
