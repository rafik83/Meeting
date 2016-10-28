<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
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
     * @var string
     */
    public $message;

    /**
     * SheetValidationView constructor.
     *
     * @param Sheet  $sheet
     * @param string $message
     */
    public function __construct(Sheet $sheet, $message)
    {
        $this->sheet   = $sheet;
        $this->message = $message;
    }
}
