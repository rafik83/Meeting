<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
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
     * @var bool
     */
    private $info = false;

    /**
     * @param int $sheetNumber
     */
    public function __construct($sheetNumber)
    {
        $this->sheetNumber = $sheetNumber;

        if ($sheetNumber > 1) {
            $this->info = true;
        }
    }

    /**
     * @return bool
     */
    public function hasInfo()
    {
        return $this->info;
    }

    /**
     * @return int
     */
    public function getSheetNumber()
    {
        return $this->sheetNumber;
    }
}
