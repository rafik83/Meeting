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

    /** @var array */
    private $sheets = [];

    public function __construct(
        int $eventId,
        int $userId,
        ?string $firstName,
        ?string $lastName,
        string $email,
        int $sheetId
    ) {
        $this->id = $eventId . '_' . $userId;
        $this->eventId = $eventId;
        $this->userId = $userId;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->addSheetId($sheetId);
    }

    public function hasSheetId(int $sheetId): bool
    {
        return isset($this->sheets[$sheetId]);
    }

    public function addSheetId(int $sheetId): void
    {
        $this->sheets[$sheetId] = ['id' => $sheetId];
    }

    public function getSheets(): array
    {
        $sheets = [];

        foreach ($this->sheets as $sheet) {
            $sheets[] = $sheet;
        }

        return $sheets;
    }
}
