<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View;

class CategoryView
{
    public $id;

    public $title;

    public function __construct($id, $title)
    {
        $this->id    = $id;
        $this->title = $title;
    }
}
