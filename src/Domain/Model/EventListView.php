<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class EventListView
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
     * @param int    $id
     * @param string $title
     */
    public function __construct($id, $title)
    {
        $this->id    = $id;
        $this->title = $title;
    }
}
