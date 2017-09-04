<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Group\Request;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\MultipleSheets\Request\SheetViewQuery;
use Proximum\Vimeet\Application\Query\MultipleSheets\Request\SheetViewQueryHandler;
use Proximum\Vimeet\Application\View\MultipleSheets\Request\SheetView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class SheetViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $locale = 'fr';
        $sheet = $this->prophesize(Sheet::class);
        $event = EventFactory::createEvent();
        $type = new Type($event);
        $sheet->getId()->willReturn(12345);
        $sheet->getTitle()->willReturn('Sheet Title');
        $sheet->getType()->willReturn($type);

        $handler = new SheetViewQueryHandler();
        $result = $handler->handle(new SheetViewQuery($sheet->reveal(), $locale));

        $expected = new SheetView(12345, 'Sheet Title', $sheet->reveal());

        $this->assertEquals($expected, $result);
    }
}
