<?php

namespace Proximum\Vimeet\Tests\Application\Query\Sheet\Attend;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Query\Sheet\Attend\SheetAttendanceViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\Attend\SheetAttendanceViewQueryHandler;
use Proximum\Vimeet\Application\View\Sheet\Attend\SheetAttendanceView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;

class SheetAttendanceViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getId()->willReturn(123);

        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesser::class);
        $sheetInfoGuesser->guessSheetTitle($sheet->reveal())->shouldBeCalled()->willReturn('sheet title');

        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository->countMeetingsToSheet($sheet->reveal())->shouldBeCalled()->willReturn(4);

        $handler = new SheetAttendanceViewQueryHandler(
            $meetingRepository->reveal(),
            $sheetInfoGuesser->reveal()
        );

        $result = $handler->handle(new SheetAttendanceViewQuery($sheet->reveal()));

        $expected = new SheetAttendanceView(
            123,
            'sheet title',
            4
        );

        $this->assertEquals($expected, $result);
    }
}
