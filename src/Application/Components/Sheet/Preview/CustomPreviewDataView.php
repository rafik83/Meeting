<?php

namespace Proximum\Vimeet\Application\Components\Sheet\Preview;

class CustomPreviewDataView
{
    /** @var string */
    public $name;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
