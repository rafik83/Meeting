<?php

namespace Proximum\Vimeet\Application\Command\Event\Sheet;

use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;
use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class IndexHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var SheetIndexerInterface */
    private $sheetIndexer;

    /** @var SheetSearchAdapterInterface */
    private $searchAdapter;

    /**
     * @param SheetRepositoryInterface    $sheetRepository
     * @param SheetIndexerInterface       $sheetIndexer
     * @param SheetSearchAdapterInterface $searchAdapter
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        SheetIndexerInterface $sheetIndexer,
        SheetSearchAdapterInterface $searchAdapter
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->sheetIndexer = $sheetIndexer;
        $this->searchAdapter = $searchAdapter;
    }

    /**
     * @param Index $command
     */
    public function handle(Index $command): void
    {
        if ($command->removeAllSheetOfEvent) {
            // The locale is hard coded as it is not used
            $sheetIds = $this->searchAdapter->getSheetIds($command->event, [], 'fr');
            $this->sheetIndexer->deleteSheets($sheetIds);
        }

        $sheets = $this->sheetRepository->getByEvent($command->event);

        foreach (array_chunk($sheets, 100, false) as $chunkSheets) {
            $this->sheetIndexer->reindexSheets($chunkSheets);
        }
    }
}
