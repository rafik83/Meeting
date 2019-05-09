<?php

namespace Proximum\Vimeet\Application\Query\Rooming\RoomingList\View;

class UserSheetTypeView
{
    /** @var int */
    public $userId;

    /** @var int */
    public $sheetId;

    /** @var null|string */
    public $firstName;

    /** @var null|string */
    public $lastName;

    /** @var null|string */
    public $sheetTitle

    /** @var null|string */;
    public $spotReference;

    /** @var string */
    public $typeTitle;

    /** @var \DateTimeInterface|null */
    public $arrival;

    /** @var \DateTimeInterface|null */
    public $departure;

    /** @var bool */
    public $hasArrivalHours;

    /** @var bool */
    public $hasDepartureHours;

    /** @var string */
    public $sheetState;

    public function __construct(
        int $userId,
        int $sheetId,
        ?string $firstName,
        ?string $lastName,
        ?string $sheetTitle,
        ?string $spotReference,
        string $typeTitle,
        ?\DateTimeInterface $arrival,
        ?\DateTimeInterface $departure,
        bool $hasArrivalHours,
        bool $hasDepartureHours,
        string $sheetState
    ) {
        $this->userId = $userId;
        $this->sheetId = $sheetId;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->sheetTitle = $sheetTitle;
        $this->spotReference = $spotReference;
        $this->typeTitle = $typeTitle;
        $this->arrival = $arrival;
        $this->departure = $departure;
        $this->hasArrivalHours = $hasArrivalHours;
        $this->hasDepartureHours = $hasDepartureHours;
        $this->sheetState = $sheetState;
    }
}
