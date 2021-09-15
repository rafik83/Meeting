<?php

namespace Proximum\Vimeet\Tests\Application\Criteria\LinkedSheets;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Criteria\LinkedSheets\AreRemovableLinkedSheetsCriteria;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\LinkedSheets;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class AreRemovableLinkedSheetsCriteriaTest extends TestCase
{
    public function test()
    {
        // prepare data
        $sheetWithNoMeeting = $this->prophesize(Sheet::class);
        $sheetWithMeeting = $this->prophesize(Sheet::class);
        $linkedSheetsRemovable = $this->prophesize(LinkedSheets::class);
        $linkedSheetsRemovable->getSheets()->shouldBeCalled()
            ->willReturn([$sheetWithNoMeeting->reveal()]);
        $linkedSheetsNotRemovable = $this->prophesize(LinkedSheets::class);
        $linkedSheetsNotRemovable->getSheets()->shouldBeCalled()
            ->willReturn([$sheetWithMeeting->reveal()]);

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);

        // prophecies dependencies

        $sheetRepository->filterWithScheduledMeetings([$sheetWithMeeting->reveal(), $sheetWithNoMeeting->reveal()])->shouldBeCalled()
            ->willReturn([$sheetWithMeeting->reveal()]);

        // run tests
        $areRemovableLinkedSheetsCriteria = new AreRemovableLinkedSheetsCriteria($sheetRepository->reveal());
        $result = $areRemovableLinkedSheetsCriteria->meetCriteria(
            [$linkedSheetsNotRemovable->reveal(), $linkedSheetsRemovable->reveal()]
        );

        $expected = [$linkedSheetsRemovable->reveal()];

        $this->assertEquals($expected, $result);
    }
}
