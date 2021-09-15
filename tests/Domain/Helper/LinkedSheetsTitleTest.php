<?php


namespace Proximum\Vimeet\Tests\Domain\Helper;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\View\Agenda\SheetMetView;
use Proximum\Vimeet\Domain\Helper\LinkedSheetsTitle;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class LinkedSheetsTitleTest extends TestCase
{
    public function test_with_linked_sheets_title()
    {
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);

        $userSheet = $this->prophesize(Sheet::class);
        $sheetMet = $this->prophesize(Sheet::class);
        $sheetMetLinkedSheet = $this->prophesize(Sheet::class);

        $sheetMetView = new SheetMetView('SheetMet', true);
        $sheetMetView2 = new SheetMetView('sheetMetLinkedSheet', false);

        $userSheet->getTitle()->willReturn('RequestedSheet');
        $sheetMet->getTitle()->willReturn('SheetMet');
        $sheetMetLinkedSheet->getTitle()->willReturn('sheetMetLinkedSheet');

        $linkedSheets = $this->prophesize(Sheet\LinkedSheets::class);
        $linkedSheets->getSheets()->shouldBeCalled()->willReturn([$sheetMet->reveal(), $sheetMetLinkedSheet->reveal()]);

        $sheetMet->hasLinkedSheets()->willReturn(true);
        $sheetMet->getLinkedSheets()->willReturn($linkedSheets->reveal());

        $requestRepository
            ->hasApprovedMeetingRequest($userSheet->reveal(), $sheetMet->reveal())
            ->shouldBeCalled()
            ->willReturn(true);

        $requestRepository
            ->hasApprovedMeetingRequest($userSheet->reveal(), $sheetMetLinkedSheet->reveal())
            ->shouldBeCalled()
            ->willReturn(false);

        $linkedSheetTitle = new LinkedSheetsTitle($requestRepository->reveal());
        $result = $linkedSheetTitle->getSheetMetViews($userSheet->reveal(), $sheetMet->reveal());

        $expected = [$sheetMetView, $sheetMetView2];

        $this->assertEquals($expected, $result);
    }

    public function test_with_no_linked_sheets_title()
    {
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);

        $userSheet = $this->prophesize(Sheet::class);
        $sheetMet = $this->prophesize(Sheet::class);

        $sheetMetView = new SheetMetView('SheetMet', false);

        $userSheet->getTitle()->willReturn('RequestedSheet');
        $sheetMet->getTitle()->willReturn('SheetMet');

        $sheetMet->hasLinkedSheets()->willReturn(false);
        $sheetMet->getLinkedSheets()->willReturn(null);

        $requestRepository
            ->hasApprovedMeetingRequest($userSheet->reveal(), $sheetMet->reveal())
            ->shouldNotBeCalled();

        $linkedSheetTitle = new LinkedSheetsTitle($requestRepository->reveal());
        $result = $linkedSheetTitle->getSheetMetViews($userSheet->reveal(), $sheetMet->reveal());

        $expected = [$sheetMetView];

        $this->assertEquals($expected, $result);
    }
}
