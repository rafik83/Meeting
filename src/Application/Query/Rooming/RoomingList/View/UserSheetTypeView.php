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

    /** @var string */
    public $sheetTitle

    /** @var null|string */;
    public $spotReference;

    /** @var string */
    public $typeTitle;

    public function __construct(
        int $userId,
        int $sheetId,
        ?string $firstName,
        ?string $lastName,
        string $sheetTitle,
        ?string $spotReference,
        string $typeTitle
    ) {
        $this->userId = $userId;
        $this->sheetId = $sheetId;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->sheetTitle = $sheetTitle;
        $this->spotReference = $spotReference;
        $this->typeTitle = $typeTitle;
    }
}
