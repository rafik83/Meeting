<?php

namespace Proximum\Vimeet\Tests\Application\Query\Sheet\Detail;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Sheet\Detail\SheetMeetingIndicatorQuery;
use Proximum\Vimeet\Application\Query\Sheet\Detail\SheetMeetingIndicatorQueryHandler;
use Proximum\Vimeet\Application\View\Sheet\Details\SheetMeetingIndicatorView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;

class SheetMeetingIndicatorQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $sheet = $this->prophesize(Sheet::class);
        $query = new SheetMeetingIndicatorQuery($sheet->reveal());

        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);

        $requestRepository->countApprovedRequestSentBySheet($query->sheet)->shouldBeCalled()->willReturn(12);
        $requestRepository->countPendingRequestSentBySheet($query->sheet)->shouldBeCalled()->willReturn(10);
        $requestRepository->countRefusedRequestSentBySheet($query->sheet)->shouldBeCalled()->willReturn(3);
        $requestRepository->countApprovedPropositionReceivedBySheet($query->sheet)->shouldBeCalled()->willReturn(9);
        $requestRepository->countPendingPropositionReceivedBySheet($query->sheet, false)->shouldBeCalled()->willReturn(5);
        $requestRepository->countRefusedPropositionReceivedBySheet($query->sheet)->shouldBeCalled()->willReturn(10);
        $meetingRepository->countMeetingsOfSheet($query->sheet)->shouldBeCalled()->willReturn(14);

        $handler = new SheetMeetingIndicatorQueryHandler(
            $requestRepository->reveal(),
            $meetingRepository->reveal()
        );

        $result = $handler->handle($query);

        $expected = new SheetMeetingIndicatorView(12, 10, 3, 9, 5, 10, 14);

        $this->assertEquals($expected, $result);
    }
}
