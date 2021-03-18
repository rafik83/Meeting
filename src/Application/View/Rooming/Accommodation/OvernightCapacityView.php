<?php

namespace Proximum\Vimeet\Application\View\Rooming\Accommodation;

class OvernightCapacityView
{
    /** @var \DateTimeInterface */
    public $date;

    /** @var int */
    public $total;

    /** @var int */
    public $remaining;

    public function __construct(
        \DateTimeInterface $date,
        int $total = 0
    ) {
        $this->date = $date;
        $this->total = $total;
        $this->remaining = $total;
    }
}
