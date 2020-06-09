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
    public $uid;

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

    /** @var array of ['id' => $sheetId] */
    private $sheets = [];

    /** @var bool */
    public $isVisio;

    /** @var bool */
    public $isVisioTested;

    /** @var array */
    public $templateObjectFilters = [];

    public function __construct(
        int $eventId,
        int $userId,
        ?string $firstName,
        ?string $lastName,
        string $email,
        string $locale,
        bool $isVisio,
        bool $isVisioTested,
        array $sheets,
        array $templateObjectFilters
    ) {
        $this->uid = self::generateId($eventId, $userId);
        $this->eventId = $eventId;
        $this->userId = $userId;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->locale = $locale;
        $this->isVisio = $isVisio;
        $this->isVisioTested = $isVisioTested;
        $this->templateObjectFilters = $templateObjectFilters;

        $this->addSheets($sheets);
    }

    public static function generateId(int $eventId, int $userId): string
    {
        return $eventId . '_' . $userId;
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
