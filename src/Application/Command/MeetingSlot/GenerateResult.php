<?php

namespace Proximum\Vimeet\Application\Command\MeetingSlot;

class GenerateResult
{
    /**
     * @var int
     */
    public $count;

    /**
     * GenerateResult constructor.
     *
     * @param int $count
     */
    public function __construct($count)
    {
        $this->count = $count;
    }
}
