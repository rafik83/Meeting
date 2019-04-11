<?php
/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Helper;

use Proximum\Vimeet\Domain\Model\Sheet;

class LinkedSheetsTitle
{
    static function getSheetTitleView(Sheet $sheet) :string
    {
        $linkedSheetTitles = [];
        if($sheet->hasLinkedSheets()){
            $linkedSheets = $sheet->getLinkedSheets();
            foreach ($linkedSheets->getSheets() as $linkedSheet) {
                $linkedSheetTitles[] = $linkedSheet->getTitle();
            }
            return implode(' - ', $linkedSheetTitles);
        }
        return $sheet->getTitle();
    }
}
