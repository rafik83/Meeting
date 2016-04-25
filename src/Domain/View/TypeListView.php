<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View;

class TypeListView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $position;

    /**
     * @var string
     */
    public $title;

    /**
     * TypeListView constructor.
     *
     * @param int    $id
     * @param int    $position
     * @param string $title
     */
    public function __construct($id, $position, $title)
    {
        $this->id       = $id;
        $this->position = $position;
        $this->title    = $title;
    }
}
