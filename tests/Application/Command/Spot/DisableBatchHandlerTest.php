<?php

namespace Proximum\Vimeet\Tests\Application\Command\Spot;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Spot\DisableBatch;
use Proximum\Vimeet\Application\Command\Spot\DisableBatchHandler;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class DisableBatchHandlerTest extends TestCase
{
    public function testHandle()
    {
        //Context
        $event = EventFactory::createEvent();
        $ids = [1, 2, 3, 4, 5];

        //Command
        $command = new DisableBatch($ids, $event);

        $spotRepository = $this->prophesize(SpotRepositoryInterface::class);
        $spotRepository->disableBatchSpot($ids, $event)->shouldBeCalled();

        //Handler
        $handle = new DisableBatchHandler($spotRepository->reveal());
        $handle->handle($command);
    }
}
