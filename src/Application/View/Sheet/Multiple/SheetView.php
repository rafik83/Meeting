<?php

namespace Proximum\Vimeet\Application\View\Sheet\Multiple;

class SheetView
{
    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /**
     * @param int    $id
     * @param string $title
     */
    public function __construct($id, $title)
    {
        $this->id = $id;
        $this->title = $title;
    }
}
