<?php

namespace Proximum\Vimeet\Application\Query\User\Event\Contact;

class UserSheetsView
{
    /** @var int */
    private $userId;

    /** @var string|null */
    private $firstName;

    /** @var string|null */
    private $lastName;

    /** @var int */
    private $sheetId;

    /** @var string|null */
    private $sheetTitle;

    /** @var int */
    private $typeId;

    public function __construct(
        int $userId,
        ?string $firstName,
        ?string $lastName,
        int $sheetId,
        ?string $sheetTitle,
        int $typeId
    ) {
        $this->userId = $userId;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->sheetId = $sheetId;
        $this->sheetTitle = $sheetTitle;
        $this->typeId = $typeId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getSheetId(): int
    {
        return $this->sheetId;
    }

    public function getSheetTitle(): ?string
    {
        return $this->sheetTitle;
    }

    public function getTypeId(): int
    {
        return $this->typeId;
    }
}
