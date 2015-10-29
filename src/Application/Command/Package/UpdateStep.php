<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Package;

use Proximum\Vimeet\Domain\Model\Sheet;

class UpdateStep
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var integer
     */
    public $step;

    /**
     * @var array
     */
    public $packageData = [];

    /**
     * @param Sheet   $sheet
     * @param integer $step
     */
    public function __construct(Sheet $sheet, $step)
    {
        $this->sheet = $sheet;
        $this->step  = $step;

        $sheetTemplate = $sheet->getType()->getPackageTemplate();
        $stepTemplate  = $sheetTemplate[$step]['template'];
        $stepData      = array_combine(array_keys($stepTemplate), array_fill(0, count($stepTemplate), null));
        $sheetData     = isset($sheet->getPackageData()[$step]) ? $sheet->getPackageData()[$step] : $stepData;

        foreach ($stepData as $key => $value) {
            $this->packageData[$key] = isset($sheetData[$key]) ? $sheetData[$key] : null;
        }
    }
}
