<?php

namespace Proximum\Vimeet\Domain\Model\User\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Scan\Type;

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

    public function isEventEntrance(): bool
    {
        return Type::TYPE_EVENT_ENTRANCE === $this->type;
    }

    public function isHappeningEntrance(): bool
    {
        return Type::TYPE_HAPPENING_ENTRANCE === $this->type;
    }

    public function getObjectId(): ?int
    {
        return $this->objectId;
    }
}
