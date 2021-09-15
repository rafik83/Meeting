<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Aggregate;

use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class SheetsAvailableSlotAggregatorHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var AvailableSlotAggregatorHandler */
    private $availableSlotAggregatorHandler;

    /** @var SheetIndexerInterface */
    private $sheetIndexer;

    /**
     * @param SheetRepositoryInterface       $sheetRepository
     * @param AvailableSlotAggregatorHandler $availableSlotAggregatorHandler
     * @param SheetIndexerInterface          $sheetIndexer
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        AvailableSlotAggregatorHandler $availableSlotAggregatorHandler,
        SheetIndexerInterface $sheetIndexer
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->availableSlotAggregatorHandler = $availableSlotAggregatorHandler;
        $this->sheetIndexer = $sheetIndexer;
    }

    /**
     * @param SheetsAvailableSlotAggregator $command
     */
    public function handle(SheetsAvailableSlotAggregator $command): void
    {
        $sheets = $this->sheetRepository->getSheetsInCatalogByEvent($command->event);

        foreach ($sheets as $sheet) {
            $this->availableSlotAggregatorHandler->handle(new AvailableSlotAggregator($sheet, false));
        }

        $this->sheetIndexer->updateSheets($sheets);
    }
}
