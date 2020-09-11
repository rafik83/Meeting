<?php

namespace Proximum\Vimeet\Application\View\Sheet;

class SheetIdsView
{
    /** @var int[] Sheet id */
    public $sheetIds;

    /*** @var bool */
    public $displayNomenclatureIds;

    public function __construct(array $sheetIds)
    {
        $this->sheetIds = $sheetIds;
    }
}
