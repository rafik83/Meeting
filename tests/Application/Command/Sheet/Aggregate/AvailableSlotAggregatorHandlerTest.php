<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet\Aggregate;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Sheet\Aggregate\AvailableSlotAggregator;
use Proximum\Vimeet\Application\Command\Sheet\Aggregate\AvailableSlotAggregatorHandler;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Sheet\Aggregate\AvailableSlotCalculator;

class AvailableSlotAggregatorHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $availableSlotCalculator = $this->prophesize(AvailableSlotCalculator::class);
        $sheet = $this->prophesize(Sheet::class);

        $availableSlotCalculator->calculateAvailableSlotForSheet($sheet->reveal(), false)->shouldBeCalled();

        $handler = new AvailableSlotAggregatorHandler($availableSlotCalculator->reveal());
        $handler->handle(new AvailableSlotAggregator($sheet->reveal(), false));
    }
}
