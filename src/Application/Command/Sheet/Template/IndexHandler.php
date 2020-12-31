<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

/**
 * Reindex all sheets by given SheetTemplate
 */
class IndexHandler
{
    /** @var SheetIndexerInterface */
    private $sheetIndexer;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     * @param SheetIndexerInterface    $sheetIndexer
     */
    public function __construct(SheetRepositoryInterface $sheetRepository, SheetIndexerInterface $sheetIndexer)
    {
        $this->sheetIndexer = $sheetIndexer;
        $this->sheetRepository = $sheetRepository;
    }

    /**
     * @param Index $index
     */
    public function handle(Index $index)
    {
        $this->sheetIndexer->updateSheets(
            $this->sheetRepository->getBySheetTemplate($index->sheetTemplate)
        );
    }
}
