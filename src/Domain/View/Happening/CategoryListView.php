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
     * @var int
     */
    public $rank;

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
     * @param int    $rank
     */
    public function __construct($id, $title, $picto, $rank)
    {
        $this->id    = $id;
        $this->title = $title;
        $this->picto = $picto;
        $this->rank  = $rank;
    }
}
