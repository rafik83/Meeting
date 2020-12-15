<?php

namespace Proximum\Vimeet\Domain\Repository\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\SheetCompleteness;

interface SheetCompletenessRepositoryInterface
{
    public function add(SheetCompleteness $sheetCompleteness): void;

    public function findCompleteness(Sheet $sheet, $locale): ?SheetCompleteness;

    public function removeForSheet(Sheet $sheet): void;
}
