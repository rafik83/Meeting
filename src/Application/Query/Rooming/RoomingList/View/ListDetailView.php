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

    /** @var SheetView[] */
    public $sheetViews;

    /** @var UserOvernightAccommodationView[] */
    public $userOvernightAccommodationViews;

    public function __construct(
        int $userId,
        ?string $firstName,
        ?string $lastName,
        ?\DateTimeInterface $arrivalDate,
        ?\DateTimeInterface $departureDate,
        ?string $comment,
        array $sheetViews,
        array $userOvernightAccommodationViews
    ) {
        $this->userId = $userId;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->arrivalDate = $arrivalDate;
        $this->departureDate = $departureDate;
        $this->comment = $comment;
        $this->sheetViews = $sheetViews;
        $this->userOvernightAccommodationViews = $userOvernightAccommodationViews;
    }

    public function addSheetView(SheetView $sheetView): void
    {
        $this->sheetViews[] = $sheetView;
    }
}
