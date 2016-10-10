<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View\Catalog;

class TypeView
{
    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /** @var int */
    public $count;

    /**
     * @param int    $id
     * @param string $title
     * @param int    $count
     */
    public function __construct($id, $title, $count)
    {
        $this->id    = $id;
        $this->title = $title;
        $this->count = $count;
    }
}
