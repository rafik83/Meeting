<?php

namespace Proximum\Vimeet\Tests\Application\Command\Event\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;
use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Application\Command\Event\Sheet\Index;
use Proximum\Vimeet\Application\Command\Event\Sheet\IndexHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class IndexHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $event = $this->prophesize(Event::class);

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetIndexer = $this->prophesize(SheetIndexerInterface::class);
        $sheetSearchAdapter = $this->prophesize(SheetSearchAdapterInterface::class);

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet3 = $this->prophesize(Sheet::class);
        $sheet4 = $this->prophesize(Sheet::class);
        $sheets = [
            $sheet1->reveal(),
            $sheet2->reveal(),
            $sheet3->reveal(),
            $sheet4->reveal(),
        ];
        $sheetRepository->getByEvent($event->reveal())->shouldBeCalled()->willReturn($sheets);
        $sheetIndexer->reindexSheets($sheets)->shouldBeCalled();

        $sheetSearchAdapter->getSheetIds($event->reveal())->shouldNotBeCalled();

        $handler = new IndexHandler(
            $sheetRepository->reveal(),
            $sheetIndexer->reveal(),
            $sheetSearchAdapter->reveal()
        );

        $handler->handle(new Index($event->reveal(), false));
    }

    public function testHandleReset(): void
    {
        $event = $this->prophesize(Event::class);

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetIndexer = $this->prophesize(SheetIndexerInterface::class);
        $sheetSearchAdapter = $this->prophesize(SheetSearchAdapterInterface::class);

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet3 = $this->prophesize(Sheet::class);
        $sheet4 = $this->prophesize(Sheet::class);
        $sheets = [
            $sheet1->reveal(),
            $sheet2->reveal(),
            $sheet3->reveal(),
            $sheet4->reveal(),
        ];
        $sheetRepository->getByEvent($event->reveal())->shouldBeCalled()->willReturn($sheets);
        $sheetIndexer->reindexSheets($sheets)->shouldBeCalled();
        $sheetIds = [1, 12, 21, 41, 59];
        $sheetSearchAdapter->getSheetIds($event->reveal(), [], 'fr')->shouldBeCalled()->willReturn($sheetIds);
        $sheetIndexer->deleteSheets($sheetIds)->shouldBeCalled();

        $handler = new IndexHandler(
            $sheetRepository->reveal(),
            $sheetIndexer->reveal(),
            $sheetSearchAdapter->reveal()
        );

        $handler->handle(new Index($event->reveal(), true));
    }
}
