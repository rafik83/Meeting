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
     * @var string
     */
    public $errorMessage;

    /**
     * @var int
     */
    public $completeness;

    /**
     * SheetValidationView constructor.
     *
     * @param Sheet  $sheet
     * @param string $message
     * @param string $errorMessage
     * @param int    $completeness
     */
    public function __construct(Sheet $sheet, $message, $errorMessage, $completeness)
    {
        $this->sheet        = $sheet;
        $this->message      = $message;
        $this->completeness = $completeness;
        $this->errorMessage = $errorMessage;
    }

    /**
     * @return bool
     */
    public function isComplete()
    {
        return $this->completeness === 100;
    }
}
