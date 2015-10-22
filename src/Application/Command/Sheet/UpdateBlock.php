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
     * @var integer
     */
    public $block;

    /**
     * @var array
     */
    public $data;

    public function __construct(Sheet $sheet, $block)
    {
        $this->sheet = $sheet;
        $this->block = $block;
        $this->data  = $sheet->getData()[$block];
    }
}
