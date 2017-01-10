<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Participant;

class MappingView
{
    /**
     * @var string
     */
    public $label;

    /**
     * @var string
     */
    public $value;

    /**
     * MappingView constructor.
     *
     * @param string $label
     * @param string $value
     */
    public function __construct($label, $value = null)
    {
        $this->label = $label;
        $this->value = $value;
    }
}
