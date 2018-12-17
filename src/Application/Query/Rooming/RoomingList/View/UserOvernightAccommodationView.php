<?php

namespace Proximum\Vimeet\Application\Query\Rooming\RoomingList\View;

class UserOvernightAccommodationView
{
    /** @var \DateTimeInterface */
    public $arrivalDate;

    /** @var \DateTimeInterface */
    public $departureDate;

    /** @var string */
    public $accommodationTitle;

    /** @var string */
    public $roomType;

    /** @var null|RoommateView */
    public $roommateView;

    public function __construct(
        \DateTimeInterface $arrivalDate,
        \DateTimeInterface $departureDate,
        string $accommodationTitle,
        string $roomType,
        ?RoommateView $roommateView = null
    ) {
        $this->arrivalDate = $arrivalDate;
        $this->departureDate = $departureDate;
        $this->accommodationTitle = $accommodationTitle;
        $this->roomType = $roomType;
        $this->roommateView = $roommateView;
    }
}
