<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Meeting;

use Proximum\Vimeet\Domain\Model\Sheet;

class StateListViewQuery
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var array
     */
    public $filters;

    /**
     * StatusListViewQuery constructor.
     *
     * @param Sheet $sheet
     * @param array $filters
     */
    public function __construct(Sheet $sheet, array $filters = [])
    {
        $this->sheet   = $sheet;
        $this->filters = $filters;
    }
}
