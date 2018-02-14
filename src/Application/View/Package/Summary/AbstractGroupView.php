<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Package\Summary;

class AbstractGroupView
{
    /**
     * @var string
     */
    public $label;

    /**
     * @var ProductView[]
     */
    public $options;

    /**
     * @var float
     */
    public $total;

    /**
     * @param string        $label
     * @param ProductView[] $options
     * @param float         $total
     */
    public function __construct($label, $options, $total)
    {
        $this->label   = $label;
        $this->options = $options;
        $this->total   = $total;
    }
}
