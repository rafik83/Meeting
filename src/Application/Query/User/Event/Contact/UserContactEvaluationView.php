<?php

namespace Proximum\Vimeet\Application\Query\User\Event\Contact;

class UserContactEvaluationView
{
    /** @var int */
    private $userId;

    /** @var string|null */
    private $firstName;

    /** @var string|null */
    private $lastName;

    /** @var array */
    private $sheetsId;

    /** @var array */
    private $sheetsTitle;

    /** @var array */
    private $categoriesTitle;

    /** @var string */
    private $typeTitle;

    /** @var int */
    private $meetingsNumber;

    public function __construct(
        int $userId,
        ?string $firstName,
        ?string $lastName,
        string $typeTitle,
        array $categoriesTitle,
        string $sheetId,
        string $sheetTitle,
        int $meetingsNumber
    ) {
        $this->userId = $userId;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->typeTitle = $typeTitle;
        $this->categoriesTitle = $categoriesTitle;
        $this->meetingsNumber = $meetingsNumber;
        $this->sheetsId[] = $sheetId;
        $this->sheetsTitle[] = $sheetTitle;
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

    public function getSheetsId(): string
    {
        return implode(', ', $this->sheetsId);
    }

    public function getSheetsTitle(): string
    {
        return implode(', ', $this->sheetsTitle);
    }
    public function getCategoriesTitle(): string
    {
        return implode(', ', $this->categoriesTitle);
    }

    public function getTypeTitle(): string
    {
        return $this->typeTitle;
    }

    public function getMeetingsNumber(): int
    {
        return $this->meetingsNumber;
    }

    public function addSheet(int $sheetId, ?string $sheetTitle): void
    {
        $this->sheetsId[] = $sheetId;
        $this->sheetsTitle[] = $sheetTitle;
    }
}
