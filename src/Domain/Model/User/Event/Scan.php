<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\User\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class Scan
{
    /** @var int */
    private $id;

    /** @var Event */
    private $event;

    /** @var User */
    private $user;

    /** @var string */
    private $type;

    /** @var int|null */
    private $objectId;

    /** @var \DateTimeInterface */
    private $scannedAt;

    /** @var \DateTimeInterface */
    private $createdAt;

    public function __construct(
        Event $event,
        User $user,
        \DateTimeInterface $scannedAt,
        \DateTimeInterface $createdAt,
        string $type,
        ?int $objectId = null
    ) {
        $this->event = $event;
        $this->user = $user;
        $this->scannedAt = $scannedAt;
        $this->createdAt = $createdAt;
        $this->type = $type;
        $this->objectId = $objectId;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getScannedAt(): \DateTimeInterface
    {
        return $this->scannedAt;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getObjectId(): ?int
    {
        return $this->objectId;
    }
}
