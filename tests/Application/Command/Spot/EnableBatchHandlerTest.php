<?php

namespace Proximum\Vimeet\Tests\Application\Command\Spot;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Spot\EnableBatch;
use Proximum\Vimeet\Application\Command\Spot\EnableBatchHandler;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class EnableBatchHandlerTest extends TestCase
{
    public function testHandle()
    {
        //Context
        $event = EventFactory::createEvent();
        $ids = [1, 2, 3, 4, 5];

        //Command
        $command = new EnableBatch($ids, $event);

        $spotRepository = $this->prophesize(SpotRepositoryInterface::class);
        $spotRepository->enableBatchSpot($ids, $event)->shouldBeCalled();

        //Handler
        $handle = new EnableBatchHandler($spotRepository->reveal());
        $handle->handle($command);
    }
}
