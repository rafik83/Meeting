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
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class LinkedSheetsTitle
{
    public static function getSheetTitleView(RequestRepositoryInterface $requestRepository, Sheet $userSheet, Sheet $sheetMet) :string
    {
        $linkedSheetTitles = [];
        if ($sheetMet->hasLinkedSheets()) {
            $linkedSheets = $sheetMet->getLinkedSheets();
            foreach ($linkedSheets->getSheets() as $othersSheet) {
                if($requestRepository->hasRequestApprovedMeeting($userSheet, $othersSheet)){
                    $linkedSheetTitles[] = '<b>'.$othersSheet->getTitle().'</b>';
                } else {
                    $linkedSheetTitles[] = $othersSheet->getTitle();
                }
            }

            return implode(' - ', $linkedSheetTitles);
        }

        return $sheetMet->getTitle();
    }
}
