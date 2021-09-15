<?php

namespace Proximum\Vimeet\Domain\Model\Event;

use Proximum\Vimeet\Domain\Model\Event;

class ExtraParameter
{
    /** @var int */
    private $id;

    /** @var Event */
    private $event;

    /** @var string */
    private $type;

    /** @var string */
    private $name;

    /** @var string */
    private $value;

    /** @var \DateTimeInterface */
    private $createdAt;

    /** @var \DateTimeInterface */
    private $updatedAt;

    /**
     * @param Event              $event
     * @param string             $type
     * @param string             $name
     * @param string             $value
     * @param \DateTimeInterface $createdAt
     */
    public function __construct(
        Event $event,
        string $type,
        string $name,
        string $value,
        \DateTimeInterface $createdAt
    ) {
        $this->event     = $event;
        $this->type      = $type;
        $this->name      = $name;
        $this->value     = $value;
        $this->createdAt = $createdAt;
        $this->updatedAt = $createdAt;
    }

    /**
     * @return int
     */
    public function getId(): int
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
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getValue(): string
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
     * @param string             $name
     * @param string             $value
     * @param \DateTimeInterface $updatedAt
     */
    public function update(string $name, string $value, \DateTimeInterface $updatedAt)
    {
        $this->name = $name;
        $this->value = $value;
        $this->updatedAt = $updatedAt;
    }
}
