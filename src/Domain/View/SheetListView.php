<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View;

class SheetListView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $type;

    /**
     * SheetListView constructor.
     *
     * @param int    $id
     * @param string $title
     * @param string $type
     */
    public function __construct($id, $title, $type)
    {
        $this->id    = $id;
        $this->title = $title;
        $this->type  = $type;
    }
}
