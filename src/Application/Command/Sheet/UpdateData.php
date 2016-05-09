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

class UpdateData
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var array
     */
    public $data;

    /**
     * UpdateData constructor.
     *
     * @param Sheet $sheet
     * @param array $data
     */
    public function __construct(Sheet $sheet, array $data)
    {
        $this->sheet = $sheet;
        $this->data  = $data;
    }
}
