<?php

namespace Proximum\Vimeet\Application\View\Meeting\Admin\Details;

class SpotView
{
    /**
     * @var string
     */
    public $ref;

    /**
     * @param string $ref
     */
    public function __construct($ref)
    {
        $this->ref = $ref;
    }
}
