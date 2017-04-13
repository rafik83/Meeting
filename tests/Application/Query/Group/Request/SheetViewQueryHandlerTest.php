<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Group\Request;

use Proximum\Vimeet\Application\Command\Planning\SheetInfoGuesserCache;
use Proximum\Vimeet\Application\Query\Group\Request\SheetViewQuery;
use Proximum\Vimeet\Application\Query\Group\Request\SheetViewQueryHandler;
use Proximum\Vimeet\Application\View\Group\Request\SheetView;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $locale = 'fr';
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getId()->willReturn(12345);
        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesserCache::class);
        $sheetInfoGuesser->guessSheetTitle($sheet->reveal(), $locale)->shouldBeCalled()->willReturn('Sheet Title');

        $handler = new SheetViewQueryHandler($sheetInfoGuesser->reveal());
        $result = $handler->handle(new SheetViewQuery($sheet->reveal(), $locale));

        $expected = new SheetView(12345, 'Sheet Title');

        $this->assertEquals($expected, $result);
    }
}
