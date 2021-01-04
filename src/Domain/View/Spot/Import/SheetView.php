<?php

namespace Proximum\Vimeet\Domain\View\Spot\Import;

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
    public function __construct(int $id, string $title)
    {
        $this->id = $id;
        $this->title = $title;
    }
}
