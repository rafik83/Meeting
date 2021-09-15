<?php

namespace Proximum\Vimeet\Application\View\AvailabilityTimeRange;

class ProductView
{
    /** @var string */
    public $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
