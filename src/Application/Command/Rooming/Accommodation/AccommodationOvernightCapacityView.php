<?php

namespace Proximum\Vimeet\Application\Command\Rooming\Accommodation;

class AccommodationOvernightCapacityView
{
    /** @var null|\DateTimeInterface */
    public $date;

    /** @var int */
    public $capacity;

    public function __construct(
        ?\DateTimeInterface $date = null,
        int $capacity = 0
    ) {
        $this->date = $date;
        $this->capacity = $capacity;
    }
}
