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
     * @var string
     */
    public $block;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var array
     */
    public $data = [];

    /**
     * @param Sheet  $sheet
     * @param string $block
     * @param string $locale
     */
    public function __construct(Sheet $sheet, $block, $locale)
    {
        $this->sheet  = $sheet;
        $this->block  = $block;
        $this->locale = $locale;

        $sheetTemplate = $sheet->getType()->getSheetTemplate();
        $blockTemplate = $sheetTemplate[$block]['template'];
        $sheetData     = isset($sheet->getData()[$block]) ? $sheet->getData()[$block] : [];

        $this->data = array_merge(
            array_combine(array_keys($blockTemplate), array_fill(0, count($blockTemplate), null)),
            array_map(function ($value) use ($locale) {
                return is_array($value) ? (isset($value[$locale]) ? $value[$locale] : null) : $value;
            }, $sheetData)
        );
    }
}
