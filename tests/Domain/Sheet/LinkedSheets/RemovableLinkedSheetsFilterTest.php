<?php

namespace Proximum\Vimeet\Tests\Domain\Sheet\LinkedSheets;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\LinkedSheets;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\LinkedSheets\RemovableLinkedSheetsFilter;

class RemovableLinkedSheetsFilterTest extends TestCase
{
    public function test()
    {
        // prepare data
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);

        $linkedSheets1 = $this->prophesize(LinkedSheets::class);
        $linkedSheets1->getSheets()->shouldBeCalled()->willReturn([$sheet1->reveal()]);
        $linkedSheets2 = $this->prophesize(LinkedSheets::class);
        $linkedSheets2->getSheets()->shouldBeCalled()->willReturn([$sheet2->reveal()]);

        // dependencies prophecies
        $sheetRepository->filterWithMeetings([$sheet1->reveal(), $sheet2->reveal()])->shouldBeCalled()
            ->willReturn([$sheet1->reveal()]);

        // run tests
        $removableLinkedSheetsFilter = new RemovableLinkedSheetsFilter($sheetRepository->reveal());
        $result = $removableLinkedSheetsFilter->isSatisfiedBy([$linkedSheets1->reveal(), $linkedSheets2->reveal()]);

        $expected = [$linkedSheets2->reveal()];

        $this->assertEquals($expected, $result);
    }
}
