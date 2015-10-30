<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;

class UpdateBlock
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var int
     */
    public $block;

    /**
     * @var array
     */
    public $data = [];

    public function __construct(Sheet $sheet, $block)
    {
        $this->sheet = $sheet;
        $this->block = $block;

        $sheetTemplate = $sheet->getType()->getSheetTemplate();
        $blockTemplate = $sheetTemplate[$block]['template'];
        $blockData     = array_combine(array_keys($blockTemplate), array_fill(0, count($blockTemplate), null));
        $sheetData     = isset($sheet->getData()[$block]) ? $sheet->getData()[$block] : $blockData;

        foreach ($blockData as $key => $value) {
            $this->data[$key] = isset($sheetData[$key]) ? $sheetData[$key] : null;
        }
    }
}
