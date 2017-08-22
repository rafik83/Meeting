<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Components\Sheet;

use Proximum\Vimeet\Application\Command\Planning\SheetInfoGuesserCache;
use Proximum\Vimeet\Application\Components\Sheet\SortedSheet;
use Proximum\Vimeet\Domain\Model\Sheet;
use PHPUnit\Framework\TestCase;

class SortedSheetTest extends TestCase
{
    public function testSort()
    {
        $sheetMock1 = $this->prophesize(Sheet::class);
        $sheetMock2 = $this->prophesize(Sheet::class);
        $sheetMock3 = $this->prophesize(Sheet::class);
        $sheetInfoGuesserCacheMock = $this->prophesize(SheetInfoGuesserCache::class);

        $sortedSheet = new SortedSheet($sheetInfoGuesserCacheMock->reveal());

        $sheetInfoGuesserCacheMock->guessSheetTitle($sheetMock1->reveal(), null)->shouldBeCalled()->willReturn('Zebra');
        $sheetInfoGuesserCacheMock->guessSheetTitle($sheetMock2->reveal(), null)->shouldBeCalled()->willReturn('Alpha');
        $sheetInfoGuesserCacheMock->guessSheetTitle($sheetMock3->reveal(), null)->shouldBeCalled()->willReturn('Beta');

        $sheets = $sortedSheet->sort([$sheetMock1->reveal(), $sheetMock2->reveal(), $sheetMock3->reveal()]);

        $expectedSortedSheets = [$sheetMock2->reveal(), $sheetMock3->reveal(), $sheetMock1->reveal()];

        $this->assertEquals($expectedSortedSheets, $sheets);
    }
}
