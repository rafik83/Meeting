<?php

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Domain\Model\Sheet;

interface SheetIndexerInterface
{
    /**
     * @param Sheet[] $sheets
     */
    public function reindexSheets(array $sheets): void;

    /**
     * @param Sheet[] $sheets
     */
    public function updateSheets(array $sheets): void;

    /**
     * @param int[] $sheetIds
     */
    public function deleteSheets(array $sheetIds): void;
}
