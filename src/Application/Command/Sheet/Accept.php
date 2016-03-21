<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;

class Accept
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * Accept constructor.
     *
     * @param Sheet $sheet
     */
    public function __construct(Sheet $sheet)
    {
        $this->sheet = $sheet;
    }
}
