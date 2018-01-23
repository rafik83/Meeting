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

class SheetsAvailableSlotAggregatorHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var AvailableSlotAggregatorHandler */
    private $availableSlotAggregatorHandler;

    /**
     * @param SheetRepositoryInterface       $sheetRepository
     * @param AvailableSlotAggregatorHandler $availableSlotAggregatorHandler
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        AvailableSlotAggregatorHandler $availableSlotAggregatorHandler
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->availableSlotAggregatorHandler = $availableSlotAggregatorHandler;
    }

    /**
     * @param SheetsAvailableSlotAggregator $command
     */
    public function handle(SheetsAvailableSlotAggregator $command)
    {
        $sheets = $this->sheetRepository->getByEvent($command->event);

        foreach ($sheets as $sheet) {
            $this->availableSlotAggregatorHandler->handle(new AvailableSlotAggregator($sheet));
        }
    }
}
