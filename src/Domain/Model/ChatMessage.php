<?php

namespace Proximum\Vimeet\Domain\Model;

use DateTimeInterface;

class ChatMessage
{
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
        ChatMessageLinkableInterface $object,
        User $createdBy,
        DateTimeInterface $createdAt,
        string $content
    ) {
        $this->objectType = $object->getObjectType();
        $this->objectId = $object->getId();
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
