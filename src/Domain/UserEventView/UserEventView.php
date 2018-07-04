<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\UserEventView;

class UserEventView
{
    /** @var string */
    public $id;

    /** @var int */
    public $eventId;

    /** @var int */
    public $userId;

    /** @var null|string */
    public $firstName;

    /** @var null|string */
    public $lastName;

    /** @var string */
    public $email;

    /** @var string */
    public $locale;

    /** @var array */
    private $sheets = [];

    public function __construct(
        int $eventId,
        int $userId,
        ?string $firstName,
        ?string $lastName,
        string $email,
        string $locale,
        array $sheets
    ) {
        $this->id = $eventId . '_' . $userId;
        $this->eventId = $eventId;
        $this->userId = $userId;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->locale = $locale;

        $this->addSheets($sheets);
    }

    public function hasSheetId(int $sheetId): bool
    {
        return isset($this->sheets[$sheetId]);
    }

    public function addSheets(array $sheets): void
    {
        foreach ($sheets as $sheet) {
            $this->addSheet($sheet);
        }
    }

    public function addSheet(array $sheet): void
    {
        $this->sheets[$sheet['id']] = $sheet;
    }

    /**
     * Get array of sheets data without the index (sheetId)
     */
    public function getSheets(): array
    {
        return array_values($this->sheets);
    }
}
