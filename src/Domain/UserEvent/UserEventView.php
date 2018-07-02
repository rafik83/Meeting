<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\UserEvent;

class UserEventView
{
    /** @var string */
    public $id;

    /** @var int */
    public $eventId;

    /** @var int */
    public $userId;

    /** @var null|string */
    public $firstname;

    /** @var null|string */
    public $lastname;

    /** @var string */
    public $email;

    public function __construct(int $eventId, int $userId, ?string $firstname, ?string $lastname, string $email)
    {
        $this->id = $eventId . '_' . $userId;
        $this->eventId = $eventId;
        $this->userId = $userId;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->email = $email;
    }
}
