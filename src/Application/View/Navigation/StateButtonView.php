<?php

namespace Proximum\Vimeet\Application\View\Navigation;

class StateButtonView
{
    /**
     * @var bool
     */
    public $state;

    /**
     * @var string
     */
    public $label;

    /**
     * StateButtonView constructor.
     *
     * @param bool   $state
     * @param string $label
     */
    public function __construct($state, $label)
    {
        $this->state = $state;
        $this->label = $label;
    }
}
