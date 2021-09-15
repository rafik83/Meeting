<?php

namespace Proximum\Vimeet\Application\View\Spot;

class SheetView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @param int    $id
     * @param string $name
     */
    public function __construct($id, $name)
    {
        $this->id   = $id;
        $this->name = $name;
    }
}
