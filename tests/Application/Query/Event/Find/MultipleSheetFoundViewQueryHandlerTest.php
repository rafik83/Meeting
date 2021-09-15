<?php

namespace Proximum\Vimeet\Tests\Application\Query\Event\Find;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Query\Event\Find\MultipleSheetFoundViewQuery;
use Proximum\Vimeet\Application\Query\Event\Find\MultipleSheetFoundViewQueryHandler;
use Proximum\Vimeet\Application\View\Event\Find\MultipleSheetsFoundView;
use Proximum\Vimeet\Application\View\Event\Find\SheetFoundView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;

class MultipleSheetFoundViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $numero  = 'Vi2017-0001';
        $event1  = $this->prophesize(Event::class);
        $event2  = $this->prophesize(Event::class);
        $sheet1  = $this->prophesize(Sheet::class);
        $sheet2  = $this->prophesize(Sheet::class);

        // Mock
        $sheet1->getId()->willReturn(1);
        $sheet2->getId()->willReturn(2);
        $sheet1->getEvent()->willReturn($event1->reveal());
        $sheet2->getEvent()->willReturn($event2->reveal());
        $event1->getId()->willReturn(1);
        $event2->getId()->willReturn(2);
        $event1->getTitle()->willReturn('title 1');
        $event2->getTitle()->willReturn('title 2');
        $event1->getFallback()->willReturn('FR');
        $event2->getFallback()->willReturn('EN');
        $guesser = $this->prophesize(SheetInfoGuesser::class);
        $guesser->guessSheetTitle($sheet1->reveal(), 'FR')->willReturn('sheet title 1');
        $guesser->guessSheetTitle($sheet2->reveal(), 'EN')->willReturn('sheet title 2');

        $sheets  = [$sheet1->reveal(), $sheet2->reveal()];
        $handler = new MultipleSheetFoundViewQueryHandler($guesser->reveal());
        $result  = $handler->handle(new MultipleSheetFoundViewQuery($numero, $sheets));

        $sheetFoundView1 = new SheetFoundView(1, 'title 1', 1, 'sheet title 1');
        $sheetFoundView2 = new SheetFoundView(2, 'title 2', 2, 'sheet title 2');
        $expected = new MultipleSheetsFoundView($numero, [
            $sheetFoundView1,
            $sheetFoundView2,
        ]);

        $this->assertEquals($expected, $result);
    }
}
