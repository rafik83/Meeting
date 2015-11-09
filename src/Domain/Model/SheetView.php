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
