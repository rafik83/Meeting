<?php

namespace Proximum\Vimeet\Domain\Model\Sheet;

use Proximum\Vimeet\Domain\Model\Event;

class ImportMapping
{
    /** @var int|null */
    private $id;

    /** @var Event */
    private $event;

    /** @var string */
    private $title;

    /** @var array */
    private $mapping;

    /** @var \DateTimeInterface */
    private $createdAt;

    /** @var \DateTimeInterface */
    private $updatedAt;

    public function __construct(
        Event $event,
        string $title,
        array $mapping,
        \DateTimeInterface $createdAt
    ) {
        $this->event = $event;
        $this->title = $title;
        $this->mapping = $mapping;
        $this->createdAt = $createdAt;
        $this->updatedAt = $createdAt;
    }

    public function update(
        array $mapping,
        \DateTimeInterface $updatedAt
    ): void {
        $this->mapping = $mapping;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getMapping(): array
    {
        return $this->mapping;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }
}
