<?php

namespace Application\Command\Sheet\PostBatch;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;
use Proximum\Vimeet\Application\Command\Sheet\PostBatch\PostBatchPending;
use Proximum\Vimeet\Application\Command\Sheet\PostBatch\PostBatchPendingHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetPendingEvent;
use Proximum\Vimeet\Tests\Factory\AdminFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PostBatchPendingHandlerTest extends TestCase
{
    public function testHandle()
    {
        $sheet1 = SheetFactory::create();
        $sheet2 = SheetFactory::create();
        $sheet3 = SheetFactory::create();
        $admin  = AdminFactory::create();

        $sheetIndexer    = $this->prophesize(SheetIndexerInterface::class);
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $datetime        = new \DateTime();

        $eventDispatcher
            ->dispatch(Events::SHEET_PENDING, Argument::type(SheetPendingEvent::class))
            ->shouldBeCalledTimes(3);

        $handler = new PostBatchPendingHandler(
            $sheetIndexer->reveal(),
            $eventDispatcher->reveal(),
            $datetime
        );

        $sheetIndexer->updateSheets([$sheet1, $sheet2, $sheet3])->shouldBeCalled();

        $handler->handle(new PostBatchPending([$sheet1, $sheet2, $sheet3], $admin));
    }
}
