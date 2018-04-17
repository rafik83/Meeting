<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\Aggregate;

use Proximum\Vimeet\Domain\Sheet\Aggregate\AvailableSlotCalculator;

class AvailableSlotAggregatorHandler
{
    /** @var AvailableSlotCalculator */
    private $availableSlotCalculator;

    /**
     * @param AvailableSlotCalculator $availableSlotCalculator
     */
    public function __construct(AvailableSlotCalculator $availableSlotCalculator)
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
