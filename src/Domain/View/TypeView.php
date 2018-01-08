<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View;

class TypeView
{
    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /** @var string */
    public $description;

    /**
     * @param int    $id
     * @param string $title
     * @param string $description
     */
    public function __construct($id, $title, $description)
    {
        $this->id          = $id;
        $this->title       = $title;
        $this->description = $description;
    }
}
