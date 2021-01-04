<?php

namespace Proximum\Vimeet\Application\View\Group\Sheet;

class SheetView
{
    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /**
     * SheetView constructor.
     *
     * @param int    $id
     * @param string $title
     */
    public function __construct($id, $title)
    {
        $this->id    = $id;
        $this->title = $title;
    }
}
