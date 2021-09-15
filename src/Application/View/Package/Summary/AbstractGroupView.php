<?php

namespace Proximum\Vimeet\Application\View\Package\Summary;

class AbstractGroupView
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
     * @var float
     */
    public $total;

    /**
     * @param string        $label
     * @param ProductView[] $options
     * @param float         $total
     */
    public function __construct($label, $options, $total)
    {
        $this->label   = $label;
        $this->options = $options;
        $this->total   = $total;
    }
}
