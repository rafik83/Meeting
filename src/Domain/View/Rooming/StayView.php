<?php

namespace Proximum\Vimeet\Domain\View\Rooming;

class StayView
{
    /** @var int */
    public $userId;

    /** @var null|string */
    public $userFirstName;

    /** @var null|string */
    public $userLastName;

    /** @var \DateTimeInterface */
    public $arrival;

    /** @var \DateTimeInterface */
    public $departure;

    /** @var string */
    public $accommodationTitle;

    /** @var string */
    public $roomType;

    public function __construct(
        int $userId,
        ?string $userFirstName,
        ?string $userLastName,
        \DateTimeInterface $arrival,
        \DateTimeInterface $departure,
        string $accommodationTitle,
        string $roomType
    ) {
        $this->userId = $userId;
        $this->userFirstName = $userFirstName;
        $this->userLastName = $userLastName;
        $this->arrival = $arrival;
        $this->departure = $departure;
        $this->accommodationTitle = $accommodationTitle;
        $this->roomType = $roomType;
    }
}
