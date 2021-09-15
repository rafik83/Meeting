<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Aggregate;

use Proximum\Vimeet\Domain\Sheet\Aggregate\AvailableSlotCalculatorInterface;

class AvailableSlotAggregatorHandler
{
    /** @var AvailableSlotCalculatorInterface */
    private $availableSlotCalculator;

    /**
     * @param AvailableSlotCalculatorInterface $availableSlotCalculator
     */
    public function __construct(AvailableSlotCalculatorInterface $availableSlotCalculator)
    {
        $this->availableSlotCalculator = $availableSlotCalculator;
    }

    /**
     * @param AvailableSlotAggregator $command
     */
    public function handle(AvailableSlotAggregator $command): void
    {
        $this->availableSlotCalculator->calculateAvailableSlotForSheet($command->sheet, $command->indexSheet);
    }
}
