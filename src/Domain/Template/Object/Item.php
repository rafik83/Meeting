<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\Object;

class Item
{
    /**
     * @var string
     */
    public $title;

    /**
     * Item constructor.
     *
     * @param string $title
     */
    public function __construct($title)
    {
        $this->title = $title;
    }
}
