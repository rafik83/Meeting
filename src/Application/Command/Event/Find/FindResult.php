<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event\Find;

use Proximum\Vimeet\Domain\Model\Sheet;

abstract class FindResult
{
    /** @var Sheet */
    public $sheet;

    /**
     * @param Sheet $sheet
     */
    public function __construct(Sheet $sheet)
    {
        $this->sheet = $sheet;
    }

    /**
     * @return bool
     */
    public function hasOnlyOneSheet()
    {
        return true;
    }

    /**
     * @return Sheet[]
     */
    public function getSheets()
    {
        return [$this->sheet];
    }
}
