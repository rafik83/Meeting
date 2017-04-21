<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet;

use Proximum\Vimeet\Application\Command\Planning\SheetInfoGuesserCache;
use Proximum\Vimeet\Domain\Model\Sheet;

class SortedSheet
{
    /** @var SheetInfoGuesserCache */
    private $sheetInfoGuesserCache;

    /**
     * @param SheetInfoGuesserCache $sheetInfoGuesserCache
     */
    public function __construct(SheetInfoGuesserCache $sheetInfoGuesserCache)
    {
        $this->sheetInfoGuesserCache = $sheetInfoGuesserCache;
    }

    /**
     * {@inheritdoc}
     */
    public function sort(&$sheets)
    {
        // Warmup sheetInfoGuesserCache
        // This avoids the issue "Warning: usort(): Array was modified by the user comparison function"
        foreach($sheets as $sheet) {
            $this->sheetInfoGuesserCache->guessSheetTitle($sheet, null);
        }

        usort($sheets, function (Sheet $one, Sheet $other) {
            return strcasecmp(
                $this->sheetInfoGuesserCache->guessSheetTitle($one, null),
                $this->sheetInfoGuesserCache->guessSheetTitle($other, null)
            );
        });
    }
}
