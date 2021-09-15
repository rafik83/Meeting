<?php

namespace Proximum\Vimeet\Domain\View\Rooming;

class AccommodationStayView
{
    /** @var int */
    public $stayId;

    /** @var \DateTimeInterface */
    public $arrival;

    /** @var \DateTimeInterface */
    public $departure;

    /** @var int */
    public $accommodationId;

    public function __construct(
        int $stayId,
        \DateTimeInterface $arrival,
        \DateTimeInterface $departure,
        int $accommodationId
    ) {
        $this->stayId = $stayId;
        $this->arrival = $arrival;
        $this->departure = $departure;
        $this->accommodationId = $accommodationId;
    }
}
