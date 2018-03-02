<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Sheet\Aggregate;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Sheet\Aggregate\AvailableSlotAggregator;
use Proximum\Vimeet\Application\Command\Sheet\Aggregate\AvailableSlotAggregatorHandler;
use Proximum\Vimeet\Application\Command\Sheet\Aggregate\SheetsAvailableSlotAggregator;
use Proximum\Vimeet\Application\Command\Sheet\Aggregate\SheetsAvailableSlotAggregatorHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class SheetsAvailableSlotAggregatorHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $availableSlotHandler = $this->prophesize(AvailableSlotAggregatorHandler::class);
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);

        $sheetRepository
            ->getByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet1->reveal(), $sheet2->reveal()])
        ;
        $availableSlotHandler->handle(new AvailableSlotAggregator($sheet1->reveal()))->shouldBeCalled();
        $availableSlotHandler->handle(new AvailableSlotAggregator($sheet2->reveal()))->shouldBeCalled();

        $handler = new SheetsAvailableSlotAggregatorHandler($sheetRepository->reveal(), $availableSlotHandler->reveal());
        $handler->handle(new SheetsAvailableSlotAggregator($event->reveal()));
    }
}
