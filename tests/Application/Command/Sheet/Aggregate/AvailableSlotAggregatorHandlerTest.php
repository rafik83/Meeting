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
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\Aggregate\AvailableSlotCalculator;

class AvailableSlotAggregatorHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $availableSlotCalculator = $this->prophesize(AvailableSlotCalculator::class);
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);

        $sheetRepository
            ->getByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet1->reveal(), $sheet2->reveal()])
        ;
        $availableSlotCalculator->calculateAvailableSlotForSheet($sheet1)->shouldBeCalled();
        $availableSlotCalculator->calculateAvailableSlotForSheet($sheet2)->shouldBeCalled();

        $handler = new AvailableSlotAggregatorHandler($sheetRepository->reveal(), $availableSlotCalculator->reveal());
        $handler->handle(new AvailableSlotAggregator($event->reveal()));
    }
}
