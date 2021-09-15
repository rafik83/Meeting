<?php

namespace Proximum\Vimeet\Application\View\AvailabilityTimeRange;

class AvailabilityTimeRangeView
{
    /** @var string */
    public $name;

    /** @var \DateTimeInterface */
    public $begin;

    /** @var \DateTimeInterface */
    public $end;

    /** @var ProductView[] */
    public $products;

    public function __construct(string $name, \DateTimeInterface $begin, \DateTimeInterface $end, array $products = [])
    {
        $this->name = $name;
        $this->begin = $begin;
        $this->end = $end;
        $this->products = $products;
    }
}
