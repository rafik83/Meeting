<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\Aggregate;

use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\Aggregate\AvailableSlotCalculator;

class AvailableSlotAggregatorHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var AvailableSlotCalculator */
    private $availableSlotCalculator;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     * @param AvailableSlotCalculator  $availableSlotCalculator
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        AvailableSlotCalculator $availableSlotCalculator
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->availableSlotCalculator = $availableSlotCalculator;
    }

    /**
     * @param AvailableSlotAggregator $command
     */
    public function handle(AvailableSlotAggregator $command)
    {
        $sheets = $this->sheetRepository->getByEvent($command->event);

        foreach ($sheets as $sheet) {
            $this->availableSlotCalculator->calculateAvailableSlotForSheet($sheet);
        }
    }
}
