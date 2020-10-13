<?php

namespace Proximum\Vimeet\Domain\Model;

use DateTimeInterface;

class ChatMessage
{
    const TYPE_PRIVATE_CHAT = 'private_chat';
    const TYPE_NETWORKING = 'networking';
    const TYPE_HAPPENING = 'happening';
    const TYPE_MEETING = 'meeting';

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

    /** @var string */
    public $authorName;

    /** @var string */
    public $sheetTitle;

    public function __construct(
        ChatMessageLinkableInterface $object,
        User $createdBy,
        DateTimeInterface $createdAt,
        string $content,
        string $authorName,
        string $sheetTitle
    ) {
        $this->objectType = $object->getObjectType();
        $this->objectId = $object->getId();
        $this->createdBy = $createdBy;
        $this->createdAt = $createdAt;
        $this->content = $content;
        $this->authorName = $authorName;
        $this->sheetTitle = $sheetTitle;
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

    public function getAuthorName(): string
    {
        return $this->authorName;
    }

    public function getSheetTitle(): string
    {
        return $this->sheetTitle;
    }
}
