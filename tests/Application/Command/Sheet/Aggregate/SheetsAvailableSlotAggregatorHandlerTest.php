<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet\Aggregate;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;
use Proximum\Vimeet\Application\Command\Sheet\Aggregate\AvailableSlotAggregator;
use Proximum\Vimeet\Application\Command\Sheet\Aggregate\AvailableSlotAggregatorHandler;
use Proximum\Vimeet\Application\Command\Sheet\Aggregate\SheetsAvailableSlotAggregator;
use Proximum\Vimeet\Application\Command\Sheet\Aggregate\SheetsAvailableSlotAggregatorHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class SheetsAvailableSlotAggregatorHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $event = $this->prophesize(Event::class);
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $availableSlotHandler = $this->prophesize(AvailableSlotAggregatorHandler::class);
        $sheetIndexer = $this->prophesize(SheetIndexerInterface::class);
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);

        $sheetRepository
            ->getSheetsInCatalogByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet1->reveal(), $sheet2->reveal()])
        ;
        $availableSlotHandler->handle(new AvailableSlotAggregator($sheet1->reveal(), false))->shouldBeCalled();
        $availableSlotHandler->handle(new AvailableSlotAggregator($sheet2->reveal(), false))->shouldBeCalled();
        $sheetIndexer->updateSheets([$sheet1->reveal(), $sheet2->reveal()])->shouldBeCalled();

        $handler = new SheetsAvailableSlotAggregatorHandler(
            $sheetRepository->reveal(),
            $availableSlotHandler->reveal(),
            $sheetIndexer->reveal()
        );
        $handler->handle(new SheetsAvailableSlotAggregator($event->reveal()));
    }
}
