<?php

namespace Proximum\Vimeet\Domain\View\Rooming;

class StayView
{
    /** @var int */
    public $stayId;

    /** @var int */
    public $userId;

    /** @var \DateTimeInterface */
    public $arrival;

    /** @var \DateTimeInterface */
    public $departure;

    /** @var string */
    public $accommodationTitle;

    /** @var string */
    public $roomType;

    /** @var string|null */
    public $roomNumber;

    public function __construct(
        int $stayId,
        int $userId,
        \DateTimeInterface $arrival,
        \DateTimeInterface $departure,
        string $accommodationTitle,
        string $roomType,
        ?string $roomNumber
    ) {
        $this->stayId = $stayId;
        $this->userId = $userId;
        $this->arrival = $arrival;
        $this->departure = $departure;
        $this->accommodationTitle = $accommodationTitle;
        $this->roomType = $roomType;
        $this->roomNumber = $roomNumber;
    }
}
