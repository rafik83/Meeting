<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet;

class CountryView
{
    /** @var string */
    public $code;

    /** @var string */
    public $name;

    public function __construct(string $name, string $code)
    {
        $this->code = $code;
        $this->name = $name;
    }
}
