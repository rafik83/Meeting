<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View\Catalog;

class CategoryView
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

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }
}
