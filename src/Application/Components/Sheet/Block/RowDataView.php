<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Block;

class RowDataView
{
    /**
     * @var string
     */
    public $label;

    /**
     * @var mixed
     */
    public $value;

    /**
     * RowView constructor.
     *
     * @param string $label
     * @param mixed  $value
     */
    public function __construct($label, $value)
    {
        $this->label = $label;
        $this->value = $value;
    }
}
