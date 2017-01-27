<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Spot;

use Proximum\Vimeet\Application\Command\Spot\DeleteBatch;
use Proximum\Vimeet\Application\Command\Spot\DeleteBatchHandler;
use Proximum\Vimeet\Application\View\Spot\Batch\DeleteBatchView;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class DeleteBatchHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testDeleteSpot()
    {
        //Context
        $event = EventFactory::createEvent();
        $ids = [1, 2, 3, 4, 5];
        $spots = [new Spot('REF', $event, 20.0, 1, 1, true)];

        $expectedView = new DeleteBatchView(
            ['REF']
        );

        //Command
        $command = new DeleteBatch($ids, $event);

        $spotRepository = $this->prophesize(SpotRepositoryInterface::class);

        $spotRepository->getSpotsByIds($ids)->willReturn($spots);

        $spotRepository->hasMeeting($spots[0])->shouldBeCalled()->willReturn(false);
        
        $spotRepository->removeBatchSpot(['REF'], $event)->shouldBeCalled();

        //Handler
        $handle = new DeleteBatchHandler($spotRepository->reveal());
        $view = $handle->handle($command);

        $this->assertEquals($view, $expectedView);
    }
    
    public function testCannotDeleteSpotWithMeeting()
    {
        //Context
        $event = EventFactory::createEvent();
        $ids = [1, 2, 3, 4, 5];
        $spots = [new Spot('REF', $event, 20.0, 1, 1, true)];

        $expectedView = new DeleteBatchView(
            [],
            ['REF']
        );

        //Command
        $command = new DeleteBatch($ids, $event);

        $spotRepository = $this->prophesize(SpotRepositoryInterface::class);

        $spotRepository->getSpotsByIds($ids)->willReturn($spots);

        $spotRepository->hasMeeting($spots[0])->shouldBeCalled()->willReturn(true);

        $spotRepository->removeBatchSpot([], $event)->shouldBeCalled();

        //Handler
        $handle = new DeleteBatchHandler($spotRepository->reveal());
        $view = $handle->handle($command);

        $this->assertEquals($view, $expectedView);
    }

    public function testCannotDeleteSpotWithSheet()
    {
        //Context
        $event = EventFactory::createEvent();
        $ids = [1, 2, 3, 4, 5];
        $spot = new Spot('REF', $event, 20.0, 1, 1, true);
        $sheet = SheetFactory::create($event);
        $spot->addSheet($sheet);

        $expectedView = new DeleteBatchView(
            [],
            [],
            ['REF']
        );

        //Command
        $command = new DeleteBatch($ids, $event);

        $spotRepository = $this->prophesize(SpotRepositoryInterface::class);

        $spotRepository->getSpotsByIds($ids)->willReturn([$spot]);

        $spotRepository->hasMeeting($spot)->shouldBeCalled()->willReturn(false);

        $spotRepository->removeBatchSpot([], $event)->shouldBeCalled();

        //Handler
        $handle = new DeleteBatchHandler($spotRepository->reveal());
        $view = $handle->handle($command);

        $this->assertEquals($view, $expectedView);
    }
}
