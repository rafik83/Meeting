<?php

namespace Proximum\Vimeet\Application\View\Package;

class GroupView
{
    /**
     * @var string
     */
    public $label;

    /**
     * @var ProductView[]
     */
    public $options;

    /**
     * @param string        $label
     * @param ProductView[] $options
     */
    public function __construct($label, $options)
    {
        $this->label   = $label;
        $this->options = $options;
    }
}
