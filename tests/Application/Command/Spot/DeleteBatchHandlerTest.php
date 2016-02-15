<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Command\Spot;

use Proximum\Vimeet\Application\Command\Spot\DeleteBatch;
use Proximum\Vimeet\Application\Command\Spot\DeleteBatchHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class DeleteBatchHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        //Context
        $event = new Event();
        $ids = [1, 2, 3, 4, 5];

        //Command
        $command = new DeleteBatch($ids, $event);

        $spotRepository = $this->prophesize(SpotRepositoryInterface::class);
        $spotRepository->removeBatchSpot($ids, $event)->shouldBeCalled();

        //Handler
        $handle = new DeleteBatchHandler($spotRepository->reveal());
        $handle->handle($command);
    }
}