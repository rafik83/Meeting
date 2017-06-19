<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Group\Request;

use Proximum\Vimeet\Application\Query\MultipleSheets\Request\SheetViewQuery;
use Proximum\Vimeet\Application\Query\MultipleSheets\Request\SheetViewQueryHandler;
use Proximum\Vimeet\Application\View\MultipleSheets\Request\SheetView;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getId()->willReturn(12345);
        $sheet->getTitle()->willReturn('Sheet Title');

        $handler = new SheetViewQueryHandler();
        $result = $handler->handle(new SheetViewQuery($sheet->reveal()));

        $expected = new SheetView(12345, 'Sheet Title', $sheet->reveal());

        $this->assertEquals($expected, $result);
    }
}
