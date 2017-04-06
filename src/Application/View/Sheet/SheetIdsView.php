<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet;

class SheetIdsView
{
    /** @var int[] Sheet id */
    public $sheetIds;

    /**
     * @param int[] $sheetIds
     */
    public function __construct(array $sheetIds)
    {
        $this->sheetIds = $sheetIds;
    }
}
