<?php

namespace Proximum\Vimeet\Domain\View\Rooming;

class TotalStaysPerPeriod
{
    /** @var \DateTimeInterface */
    public $arrival;

    /** @var \DateTimeInterface */
    public $departure;

    /** @var int */
    public $totalStays;

    public function __construct(
        \DateTimeInterface $arrival,
        \DateTimeInterface $departure,
        int $totalStays
    ) {
        $this->arrival = $arrival;
        $this->departure = $departure;
        $this->totalStays = $totalStays;
    }
}
