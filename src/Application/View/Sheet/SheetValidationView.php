<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;

class SheetValidationView
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var int
     */
    private $completeness;

    /**
     * SheetValidationView constructor.
     *
     * @param Sheet $sheet
     * @param int   $completeness
     */
    public function __construct(Sheet $sheet, $completeness)
    {
        $this->sheet        = $sheet;
        $this->completeness = $completeness;
    }

    /**
     * @return bool
     */
    public function isComplete()
    {
        return 100 === $this->completeness;
    }
}
