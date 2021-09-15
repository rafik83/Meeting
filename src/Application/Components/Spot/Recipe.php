<?php

namespace Proximum\Vimeet\Application\Components\Spot;

class Recipe
{
    /**
     * @var string
     */
    public $prefix;

    /**
     * @var int
     */
    public $start;

    /**
     * @var int
     */
    public $end;

    /**
     * Recipe constructor.
     *
     * @param string $prefix
     * @param int    $start
     * @param int    $end
     */
    public function __construct($prefix, $start = null, $end = null)
    {
        $this->prefix = $prefix;
        $this->start  = $start;
        $this->end    = $end;
    }
}
