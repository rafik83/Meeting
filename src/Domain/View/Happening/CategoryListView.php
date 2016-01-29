<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View\Happening;

class CategoryListView
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
    public $picto;

    /**
     * CategoryListView constructor.
     *
     * @param int    $id
     * @param string $title
     * @param string $picto
     */
    public function __construct($id, $title, $picto)
    {
        $this->id    = $id;
        $this->title = $title;
        $this->picto = $picto;
    }
}
