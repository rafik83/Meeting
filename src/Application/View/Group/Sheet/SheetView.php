<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Group\Sheet;

class SheetView
{
    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /**
     * SheetView constructor.
     *
     * @param int    $id
     * @param string $title
     */
    public function __construct($id, $title)
    {
        $this->id    = $id;
        $this->title = $title;
    }
}
