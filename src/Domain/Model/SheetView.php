<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class SheetView
{
    public $id;

    public $typeTitle;

    public function __construct($id, $typeTitle)
    {
        $this->id        = $id;
        $this->typeTitle = $typeTitle;
    }
}
