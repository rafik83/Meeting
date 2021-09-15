<?php

namespace Proximum\Vimeet\Domain\View;

class CategoryView
{
    public $id;

    public $title;

    public function __construct($id, $title)
    {
        $this->id    = $id;
        $this->title = $title;
    }
}
