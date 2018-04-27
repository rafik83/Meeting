<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
     * @var string|null
     */
    public $value;

    /**
     * MappingView constructor.
     *
     * @param string      $label
     * @param string|null $value
     */
    public function __construct($label, $value = null)
    {
        $this->label = $label;
        $this->value = $value;
    }
}
