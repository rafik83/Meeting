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

    /** @var UserStayView[] */
    public $userStayViews;

    public function __construct(
        int $userId,
        ?string $firstName,
        ?string $lastName,
        ?\DateTimeInterface $arrivalDate,
        ?\DateTimeInterface $departureDate,
        ?string $comment,
        array $sheetViews,
        array $userStayViews
    ) {
        $this->userId = $userId;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->arrivalDate = $arrivalDate;
        $this->departureDate = $departureDate;
        $this->comment = $comment;
        $this->sheetViews = $sheetViews;
        $this->userStayViews = $userStayViews;
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
