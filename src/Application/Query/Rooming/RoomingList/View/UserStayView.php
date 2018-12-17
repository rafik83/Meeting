<?php

namespace Proximum\Vimeet\Application\Query\Rooming\RoomingList\View;

class UserStayView
{
    /** @var int */
    public $stayId;

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
        int $stayId,
        \DateTimeInterface $arrivalDate,
        \DateTimeInterface $departureDate,
        string $accommodationTitle,
        string $roomType,
        ?RoommateView $roommateView = null
    ) {
        $this->stayId = $stayId;
        $this->arrivalDate = $arrivalDate;
        $this->departureDate = $departureDate;
        $this->accommodationTitle = $accommodationTitle;
        $this->roomType = $roomType;
        $this->roommateView = $roommateView;
    }
}
