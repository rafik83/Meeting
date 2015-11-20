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
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $eventTitle;

    /**
     * TypeListView constructor.
     *
     * @param int    $id
     * @param string $title
     * @param string $eventTitle
     */
    public function __construct($id, $title, $eventTitle)
    {
        $this->id         = $id;
        $this->title      = $title;
        $this->eventTitle = $eventTitle;
    }
}
