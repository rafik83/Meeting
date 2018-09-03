<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\UserEventView;

class UserEventListView
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

    /** @var UserEventSheetsListView[] */
    public $userEventSheetsListViews;

    public function __construct(
        int $eventId,
        int $userId,
        ?string $firstName,
        ?string $lastName,
        string $email,
        string $locale,
        array $userEventSheetsListViews
    ) {
        $this->id = UserEventView::generateId($eventId, $userId);
        $this->eventId = $eventId;
        $this->userId = $userId;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->locale = $locale;
        $this->userEventSheetsListViews = $userEventSheetsListViews;
    }
}
