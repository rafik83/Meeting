<?php

namespace Proximum\Vimeet\Application\View\Meeting;

class StateListView
{
    /**
     * @var string
     */
    public $state;

    /**
     * @var int
     */
    public $count;

    /**
     * StateListView constructor.
     *
     * @param $state
     * @param $count
     */
    public function __construct($state, $count)
    {
        $this->state = $state;
        $this->count = $count;
    }
}
