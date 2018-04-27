<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Catalog;

class LocalizationView
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
     * LocalizationView constructor.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->id   = strtolower(str_replace(' ', '-', $name));
        $this->name = $name;
    }
}
