<?php

namespace Proximum\Vimeet\Application\View\Planner\Result;

class IdResult
{
    /** @var int */
    public $id;

    /**
     * @param int $id
     */
    public function __construct($id)
    {
        $this->id = intval($id);
    }
}
