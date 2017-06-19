<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\MultipleSheets\Request;

use Proximum\Vimeet\Application\View\MultipleSheets\Request\SheetView;

class SheetViewQueryHandler
{
    /**
     * @param SheetViewQuery $query
     *
     * @return SheetView
     */
    public function handle(SheetViewQuery $query)
    {
        return new SheetView(
            $query->sheet->getId(),
            $query->sheet->getTitle(),
            $query->sheet
        );
    }
}
