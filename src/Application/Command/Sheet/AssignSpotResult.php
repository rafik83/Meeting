<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

class AssignSpotResult
{
    /**
     * @var int
     */
    private $sheetNumber;

    /**
     * @param int $sheetNumber
     */
    public function __construct($sheetNumber)
    {
        $this->sheetNumber = $sheetNumber;
    }

    /**
     * @return bool
     */
    public function hasInfo()
    {
        return $this->sheetNumber > 1;
    }

    /**
     * @return int
     */
    public function getSheetNumber()
    {
        return $this->sheetNumber;
    }
}
