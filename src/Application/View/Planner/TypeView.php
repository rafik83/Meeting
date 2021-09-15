<?php

namespace Proximum\Vimeet\Application\View\Planner;

class TypeView
{
    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /** @var string */
    public $reference;

    /**
     * @param int    $id
     * @param string $title
     */
    public function __construct($id, $title)
    {
        $this->id        = $id;
        $this->title     = $title;
        $this->reference = sprintf('type%s', $id);
    }
}
