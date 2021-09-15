<?php

namespace Proximum\Vimeet\Application\Query\Rooming\RoomingList\View;

class ListDetailView
{
    /** @var int */
    public $userId;

    /** @var null|string */
    public $firstName;

    /** @var null|string */
    public $lastName;

    /** @var \DateTimeInterface|null */
    public $arrivalDate;

    /** @var \DateTimeInterface|null */
    public $departureDate;

    /** @var null|string */
    public $comment;

    /** @var null|string */
    public $tasting;

    /** @var SheetView[] */
    public $sheetViews;

    /** @var UserStayView[] */
    public $userStayViews;

    /** @var bool */
    public $hasArrivalHours;

    /** @var bool */
    public $hasDepartureHours;

    /** @var bool */
    public $areDatesFilledByUser;

    public function __construct(
        int $userId,
        ?string $firstName,
        ?string $lastName,
        ?\DateTimeInterface $arrivalDate,
        ?\DateTimeInterface $departureDate,
        bool $areDatesFilledByUser,
        bool $hasArrivalHours,
        bool $hasDepartureHours,
        ?string $comment,
        ?string $tasting,
        array $sheetViews,
        array $userStayViews
    ) {
        $this->userId = $userId;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->arrivalDate = $arrivalDate;
        $this->departureDate = $departureDate;
        $this->areDatesFilledByUser = $areDatesFilledByUser;
        $this->comment = $comment;
        $this->tasting = $tasting;
        $this->sheetViews = $sheetViews;
        $this->userStayViews = $userStayViews;
        $this->hasArrivalHours = $hasArrivalHours;
        $this->hasDepartureHours = $hasDepartureHours;
    }

    public function addSheetView(SheetView $sheetView): void
    {
        $this->sheetViews[] = $sheetView;
    }

    public function addUserStayView(UserStayView $userStayView): void
    {
        $this->userStayViews[] = $userStayView;
    }
}
