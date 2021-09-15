<?php

namespace Proximum\Vimeet\Domain\Model\Event;

use Proximum\Vimeet\Domain\Model\Event;

class ExtraData
{
    /** @var null|int */
    private $id;

    /** @var Event */
    private $event;

    /** @var string */
    private $name;

    /** @var string|null */
    private $value;

    /** @var \DateTimeInterface */
    private $createdAt;

    /** @var \DateTimeInterface */
    private $updatedAt;

    /**
     * @param Event              $event
     * @param string             $name      one of Proximum\Vimeet\Domain\Event\ExtraData\Type constant
     * @param string|null        $value
     * @param \DateTimeInterface $createdAt
     */
    public function __construct(Event $event, string $name, ?string $value, \DateTimeInterface $createdAt)
    {
        $this->event = $event;
        $this->name = $name;
        $this->value = $value;
        $this->createdAt = $createdAt;
        $this->updatedAt = $createdAt;
    }

    /**
     * @return null|int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Event
     */
    public function getEvent(): Event
    {
        return $this->event;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return null|string
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @param null|string        $value
     * @param \DateTimeInterface $updatedAt
     */
    public function update(?string $value, \DateTimeInterface $updatedAt): void
    {
        $this->value = $value;
        $this->updatedAt = $updatedAt;
    }
}
