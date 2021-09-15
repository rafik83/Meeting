<?php

namespace Proximum\Vimeet\Tests\Application\Query\Analytic\MeetingSolution\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\Sheet\SheetSatisfactionListQuery;
use Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\Sheet\SheetSatisfactionListQueryHandler;
use Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\Sheet\SheetSatisfactionViewQuery;
use Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\Sheet\SheetSatisfactionViewQueryHandler;
use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Sheet\SheetSatisfactionView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class SheetSatisfactionListQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $event->getFallback()->willReturn('fr');
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet3 = $this->prophesize(Sheet::class);

        $sheet1->getId()->willReturn(1);
        $sheet2->getId()->willReturn(2);
        $sheet3->getId()->willReturn(3);

        $sheets = [$sheet1->reveal(), $sheet2->reveal(), $sheet3->reveal()];

        // Mock
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $sheetSatisfactionViewQueryHandler = $this->prophesize(SheetSatisfactionViewQueryHandler::class);

        $sheetRepository
            ->getSheetsInCatalogByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn($sheets)
        ;

        $requestRepository
            ->countApprovedRequestBySheets($event->reveal(), $sheets)
            ->shouldBeCalled()
            ->willReturn([
                1 => ['countRequest' => 12],
                2 => ['countRequest' => 18],
                3 => ['countRequest' => 22],
            ])
        ;

        $meetingRepository
            ->countMeetingBySheets($event->reveal(), $sheets)
            ->shouldBeCalled()
            ->willReturn([
                2 => ['countMeetings' => 10],
                3 => ['countMeetings' => 17],
            ])
        ;

        $sheetSatisfactionView1 = new SheetSatisfactionView(1, 'sheet1', 11, 'type1', 0);
        $sheetSatisfactionView2 = new SheetSatisfactionView(2, 'sheet2', 11, 'type1', 55);
        $sheetSatisfactionView3 = new SheetSatisfactionView(3, 'sheet3', 12, 'type2', 77);
        $sheetSatisfactionViewQueryHandler
            ->handle(new SheetSatisfactionViewQuery($sheet1->reveal(), 12, 0, 'fr'))
            ->shouldBeCalled()
            ->willReturn($sheetSatisfactionView1);

        $sheetSatisfactionViewQueryHandler
            ->handle(new SheetSatisfactionViewQuery($sheet2->reveal(), 18, 10, 'fr'))
            ->shouldBeCalled()
            ->willReturn($sheetSatisfactionView2);

        $sheetSatisfactionViewQueryHandler
            ->handle(new SheetSatisfactionViewQuery($sheet3->reveal(), 22, 17, 'fr'))
            ->shouldBeCalled()
            ->willReturn($sheetSatisfactionView3);

        $handler = new SheetSatisfactionListQueryHandler(
            $sheetRepository->reveal(),
            $requestRepository->reveal(),
            $meetingRepository->reveal(),
            $sheetSatisfactionViewQueryHandler->reveal()
        );
        $result = $handler->handle(new SheetSatisfactionListQuery($event->reveal()));

        $expected = [
            $sheetSatisfactionView1,
            $sheetSatisfactionView2,
            $sheetSatisfactionView3,
        ];

        $this->assertEquals($expected, $result);
    }
}
