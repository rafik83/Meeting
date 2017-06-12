<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\SheetGroup;

use Proximum\Vimeet\Domain\Model\Sheet;

class RemoveSheetFromGroupChecker
{
    /**
     * @param Sheet $sheet
     *
     * @return bool
     */
    public function canRemoveSheetFromGroup(Sheet $sheet)
    {
        return false;
    }
}
