<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Application\View\Sheet\SheetValidationView;

class SheetValidationViewQueryHandler
{
    const SHEET_COMPLETE_MESSAGE   = 'sheet.validation.complete.message';
    const SHEET_UNCOMPLETE_MESSAGE = 'sheet.validation.uncomplete.message';

    /**
     * @param SheetValidationViewQuery $query
     *
     * @return SheetValidationView
     */
    public function handle(SheetValidationViewQuery $query)
    {
        return new SheetValidationView($query->sheet, self::SHEET_COMPLETE_MESSAGE);
    }
}
