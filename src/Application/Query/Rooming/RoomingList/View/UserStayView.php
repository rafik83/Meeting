<?php

namespace Proximum\Vimeet\Application\Query\Rooming\RoomingList\View;

class UserStayView extends AbstractUserStayView
{
    /** @var int */
    public $stayId;

    /** @var string */
    public $accommodationTitle;

    /** @var string */
    public $roomType;

    /** @var null|RoommateView */
    public $roommateView;

    /** @var string|null */
    public $roomNumber;

    public function __construct(
        int $stayId,
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        string $accommodationTitle,
        string $roomType,
        ?string $roomNumber,
        ?RoommateView $roommateView = null
    ) {
        parent::__construct($begin, $end);

        $this->stayId = $stayId;
        $this->accommodationTitle = $accommodationTitle;
        $this->roomType = $roomType;
        $this->roommateView = $roommateView;
        $this->roomNumber = $roomNumber;
    }

    public function isAssigned(): bool
    {
        return true;
    }
}
