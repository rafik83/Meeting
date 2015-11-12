<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet as SheetModel;
use Proximum\Vimeet\Domain\Model\SheetDataView;

class Sheet
{
    /**
     * @param SheetModel $sheet
     * @param string     $locale
     *
     * @return SheetDataView
     */
    public function getSheetDataView(SheetModel $sheet, $locale)
    {
        return new SheetDataView(
            $sheet->getId(),
            $sheet->getEvent(),
            $sheet->getType(),
            $sheet->getParticipants(),
            $sheet->getData(),
            $sheet->getPackageData(),
            $sheet->getBillingData()
        );
    }
}
