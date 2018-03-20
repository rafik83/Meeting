<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet;

class CountryView
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->id   = strtolower(str_replace(' ', '-', $name));
        $this->name = $name;
    }
}
