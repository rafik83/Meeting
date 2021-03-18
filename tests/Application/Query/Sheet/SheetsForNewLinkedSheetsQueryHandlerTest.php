<?php

namespace Proximum\Vimeet\Tests\Application\Query\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Sheet\SheetsForNewLinkedSheetsQuery;
use Proximum\Vimeet\Application\Query\Sheet\SheetsForNewLinkedSheetsQueryHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\View\SheetView;

class SheetsForNewLinkedSheetsQueryHandlerTest extends TestCase
{
    public function test()
    {
        // init
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $event = $this->prophesize(Event::class);
        $sheet = $this->prophesize(Sheet::class);

        // mock data
        $sheet->getId()->shouldBeCalled()->willReturn(42);
        $sheet->getTitle()->shouldBeCalled()->willReturn('SquareSoft');

        // predictions
        $sheetRepository->getNotLinkedSheets($event->reveal())->shouldBeCalled()->willReturn([$sheet]);

        // run
        $query = new SheetsForNewLinkedSheetsQuery($event->reveal());
        $handler = new SheetsForNewLinkedSheetsQueryHandler($sheetRepository->reveal());
        $result = $handler->handle($query);

        // final tests
        $expected = [
          new SheetView(42, 'SquareSoft')
        ];

        $this->assertEquals($expected, $result);
    }
}
