<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Package;

class GroupView
{
    /**
     * @var string
     */
    public $label;

    /**
     * @var OptionView[]
     */
    public $options;

    /**
     * @param string       $label
     * @param OptionView[] $options
     */
    public function __construct($label, $options)
    {
        $this->label   = $label;
        $this->options = $options;
    }
}
