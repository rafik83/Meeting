<?php

namespace Proximum\Vimeet\Application\Query\User\Event\Contact;

class UserContactEvaluationStatsView
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

    /** @var int */
    private $contactsScannedNumber;

    /** @var int */
    private $contactsEvaluated5;

    /** @var int */
    private $contactsEvaluated4;

    /** @var int */
    private $contactsEvaluated3;

    /** @var int */
    private $contactsEvaluated2;

    /** @var int */
    private $contactsEvaluated1;

    /** @var int */
    private $contactsNotEvaluated;

    public function __construct(
        int $userId,
        ?string $firstName,
        ?string $lastName,
        string $typeTitle,
        array $categoriesTitle,
        string $sheetId,
        string $sheetTitle,
        int $meetingsNumber,
        int $contactsScannedNumber,
        int $contactsEvaluated5,
        int $contactsEvaluated4,
        int $contactsEvaluated3,
        int $contactsEvaluated2,
        int $contactsEvaluated1,
        int $contactsNotEvaluated
    ) {
        $this->userId = $userId;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->typeTitle = $typeTitle;
        $this->categoriesTitle = $categoriesTitle;
        $this->meetingsNumber = $meetingsNumber;
        $this->sheetsId[] = $sheetId;
        $this->sheetsTitle[] = $sheetTitle;
        $this->contactsScannedNumber = $contactsScannedNumber;
        $this->contactsEvaluated5 = $contactsEvaluated5;
        $this->contactsEvaluated4 = $contactsEvaluated4;
        $this->contactsEvaluated3 = $contactsEvaluated3;
        $this->contactsEvaluated2 = $contactsEvaluated2;
        $this->contactsEvaluated1 = $contactsEvaluated1;
        $this->contactsNotEvaluated = $contactsNotEvaluated;
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

    public function getContactsScannedNumber(): int
    {
        return $this->contactsScannedNumber;
    }

    public function getContactsEvaluated5(): int
    {
        return $this->contactsEvaluated5;
    }

    public function getContactsEvaluated4(): int
    {
        return $this->contactsEvaluated4;
    }

    public function getContactsEvaluated3(): int
    {
        return $this->contactsEvaluated3;
    }

    public function getContactsEvaluated2(): int
    {
        return $this->contactsEvaluated2;
    }

    public function getContactsEvaluated1(): int
    {
        return $this->contactsEvaluated1;
    }

    public function getContactsNotEvaluated(): int
    {
        return $this->contactsNotEvaluated;
    }

    public function addSheet(int $sheetId, ?string $sheetTitle): void
    {
        $this->sheetsId[] = $sheetId;
        $this->sheetsTitle[] = $sheetTitle;
    }
}
