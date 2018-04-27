<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View;

class SheetView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $typeTitle;

    /**
     * SheetView constructor.
     *
     * @param int    $id
     * @param string $typeTitle
     */
    public function __construct($id, $typeTitle)
    {
        $this->id        = $id;
        $this->typeTitle = $typeTitle;
    }
}
