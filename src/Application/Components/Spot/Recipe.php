<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Spot;

class Recipe
{
    /**
     * @var string
     */
    public $prefix;

    /**
     * @var int
     */
    public $begin;

    /**
     * @var int
     */
    public $end;

    /**
     * Recipe constructor.
     *
     * @param string $prefix
     * @param int    $begin
     * @param int    $end
     */
    public function __construct($prefix, $begin = null, $end = null)
    {
        $this->prefix = $prefix;
        $this->begin  = $begin;
        $this->end    = $end;
    }
}
