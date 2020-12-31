<?php

namespace Proximum\Vimeet\Application\View\Participant;

class MappingView
{
    /**
     * @var string
     */
    public $label;

    /**
     * @var string|null
     */
    public $value;

    /**
     * MappingView constructor.
     *
     * @param string      $label
     * @param string|null $value
     */
    public function __construct($label, $value = null)
    {
        $this->label = $label;
        $this->value = $value;
    }
}
