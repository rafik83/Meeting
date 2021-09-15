<?php

namespace Proximum\Vimeet\Tests\Application\Command\Spot;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Spot\DeleteBatch;
use Proximum\Vimeet\Application\Command\Spot\DeleteBatchHandler;
use Proximum\Vimeet\Application\View\Spot\Batch\DeleteBatchView;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class DeleteBatchHandlerTest extends TestCase
{
    public function testDeleteSpot()
    {
        //Context
        $event = EventFactory::createEvent();
        $ids = [1, 2, 3, 4, 5];
        $spot = new Spot('REF', $event, 20.0, 1, 1, true);

        $expectedView = new DeleteBatchView();
        $expectedView->addDeletedSpot($spot);

        //Command
        $command = new DeleteBatch($ids, $event);

        $spotRepository = $this->prophesize(SpotRepositoryInterface::class);

        $spotRepository->getSpotsByIds($ids)->willReturn([$spot]);

        $spotRepository->hasMeeting($spot)->shouldBeCalled()->willReturn(false);

        $spotRepository->removeBatchSpot([$spot], $event)->shouldBeCalled();

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
        $spot = new Spot('REF', $event, 20.0, 1, 1, true);

        $expectedView = new DeleteBatchView();
        $expectedView->addSpotWithMeeting($spot);

        //Command
        $command = new DeleteBatch($ids, $event);

        $spotRepository = $this->prophesize(SpotRepositoryInterface::class);

        $spotRepository->getSpotsByIds($ids)->willReturn([$spot]);

        $spotRepository->hasMeeting($spot)->shouldBeCalled()->willReturn(true);

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
        $ids   = [1, 2, 3, 4, 5];
        $spot  = new Spot('REF', $event, 20.0, 1, 1, true);
        $sheet = SheetFactory::create($event);
        $spot->addSheet($sheet);

        $expectedView = new DeleteBatchView();
        $expectedView->addSpotWithSheets($spot);

        //Command
        $command = new DeleteBatch($ids, $event);

        $spotRepository = $this->prophesize(SpotRepositoryInterface::class);

        $spotRepository->getSpotsByIds($ids)->willReturn([$spot]);

        $spotRepository->removeBatchSpot([], $event)->shouldBeCalled();

        //Handler
        $handle = new DeleteBatchHandler($spotRepository->reveal());
        $view = $handle->handle($command);

        $this->assertEquals($view, $expectedView);
    }
}
