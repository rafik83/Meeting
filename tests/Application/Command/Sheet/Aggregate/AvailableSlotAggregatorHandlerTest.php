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
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Sheet\Aggregate\AvailableSlotCalculator;

class AvailableSlotAggregatorHandlerTest extends TestCase
{
    public function testHandle()
    {
        $availableSlotCalculator = $this->prophesize(AvailableSlotCalculator::class);
        $sheet = $this->prophesize(Sheet::class);

        $availableSlotCalculator->calculateAvailableSlotForSheet($sheet->reveal())->shouldBeCalled();

        $handler = new AvailableSlotAggregatorHandler($availableSlotCalculator->reveal());
        $handler->handle(new AvailableSlotAggregator($sheet->reveal()));
    }
}
