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
     * @var bool
     */
    public $hidden;

    /**
     * TypeListView constructor.
     *
     * @param int    $id
     * @param int    $position
     * @param string $title
     * @param bool   $hidden
     */
    public function __construct($id, $position, $title, $hidden)
    {
        $this->id       = $id;
        $this->position = $position;
        $this->title    = $title;
        $this->hidden   = $hidden;
    }
}
