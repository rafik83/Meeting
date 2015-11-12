<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class TypeListView
{
    public $id;

    public $title;

    public $eventTitle;

    public function __construct($id, $title, $eventTitle)
    {
        $this->id         = $id;
        $this->title      = $title;
        $this->eventTitle = $eventTitle;
    }
}
