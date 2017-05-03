<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\MultipleSheets\Request;

use Proximum\Vimeet\Application\Command\Planning\SheetInfoGuesserCache;
use Proximum\Vimeet\Application\View\MultipleSheets\Request\SheetView;

class SheetViewQueryHandler
{
    /** @var SheetInfoGuesserCache */
    private $sheetInfoGuesser;

    /**
     * @param SheetInfoGuesserCache $sheetInfoGuesser
     */
    public function __construct(SheetInfoGuesserCache $sheetInfoGuesser)
    {
        $this->sheetInfoGuesser = $sheetInfoGuesser;
    }

    /**
     * @param SheetViewQuery $query
     *
     * @return SheetView
     */
    public function handle(SheetViewQuery $query)
    {
        return new SheetView(
            $query->sheet->getId(),
            $this->sheetInfoGuesser->guessSheetTitle($query->sheet, $query->locale),
            $query->sheet
        );
    }
}
