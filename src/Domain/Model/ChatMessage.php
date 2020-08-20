<?php

namespace Proximum\Vimeet\Domain\Model;

use DateTimeInterface;

class ChatMessage
{
    public const OBJECT_TYPE_MEETING = 'meeting';
    public const OBJECT_TYPE_HAPPENING = 'happening';

    public const ALL_OBJECT_TYPES = [
        self::OBJECT_TYPE_MEETING,
        self::OBJECT_TYPE_HAPPENING,
    ];

    /** @var int */
    private $id;

    /** @var string */
    private $objectType;

    /** @var int */
    private $objectId;

    /** @var User */
    private $createdBy;

    /** @var DateTimeInterface */
    private $createdAt;

    /** @var string */
    private $content;

    public function __construct(
        string $objectType,
        int $objectId,
        User $createdBy,
        DateTimeInterface $createdAt,
        string $content
    ) {
        if (!in_array($objectType, self::ALL_OBJECT_TYPES, true)) {
            throw new \InvalidArgumentException('ObjectType invalid');
        }

        $this->objectType = $objectType;
        $this->objectId = $objectId;
        $this->createdBy = $createdBy;
        $this->createdAt = $createdAt;
        $this->content = $content;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getObjectType(): string
    {
        return $this->objectType;
    }

    public function getObjectId(): int
    {
        return $this->objectId;
    }

    public function getCreatedBy(): User
    {
        return $this->createdBy;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getContent(): string
    {
        return $this->content;
    }

}
