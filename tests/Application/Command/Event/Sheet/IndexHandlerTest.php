<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Event\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;
use Proximum\Vimeet\Application\Command\Event\Sheet\Index;
use Proximum\Vimeet\Application\Command\Event\Sheet\IndexHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class IndexHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetIndexer = $this->prophesize(SheetIndexerInterface::class);

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet3 = $this->prophesize(Sheet::class);
        $sheet4 = $this->prophesize(Sheet::class);
        $sheets = [
            $sheet1->reveal(),
            $sheet2->reveal(),
            $sheet3->reveal(),
            $sheet4->reveal(),
        ];
        $sheetRepository->getByEvent($event->reveal())->shouldBeCalled()->willReturn($sheets);
        $sheetIndexer->updateSheets($sheets)->shouldBeCalled();

        $handler = new IndexHandler(
            $sheetRepository->reveal(),
            $sheetIndexer->reveal()
        );

        $handler->handle(new Index($event->reveal()));
    }
}
