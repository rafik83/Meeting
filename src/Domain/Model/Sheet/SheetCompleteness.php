<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;

class SheetCompleteness
{
    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var integer
     */
    private $completeness;

    /**
     * SheetCompleteness constructor.
     *
     * @param Sheet  $sheet
     * @param string $locale
     * @param int    $completeness
     */
    public function __construct(Sheet $sheet, $locale, $completeness)
    {
        $this->sheet        = $sheet;
        $this->locale       = $locale;
        $this->completeness = $completeness;
    }
}

