<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Block;

class BlockDataView
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var RowDataView[]
     */
    public $rows;

    /**
     * BlockView constructor.
     *
     * @param string        $title
     * @param RowDataView[] $rows
     */
    public function __construct($title, array $rows)
    {
        $this->title = $title;
        $this->rows  = $rows;
    }
}
