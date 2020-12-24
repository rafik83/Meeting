<?php

namespace Proximum\Vimeet\Domain\Model\User\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class ExtraData
{
    /** @var int */
    private $id;

    /** @var User */
    private $user;

    /** @var Event */
    private $event;

    /** @var string one of Proximum\Vimeet\Domain\User\Event\ExtraData\Type */
    private $name;

    /** @var string|null */
    private $value;

    /** @var \DateTimeInterface */
    private $createdAt;

    /** @var \DateTimeInterface */
    private $updatedAt;

    /**
     * @param User               $user
     * @param Event              $event
     * @param string             $name
     * @param string|null        $value
     * @param \DateTimeInterface $createdAt
     */
    public function __construct(User $user, Event $event, string $name, string $value = null, \DateTimeInterface $createdAt)
    {
        $this->user = $user;
        $this->event = $event;
        $this->name = $name;
        $this->value = $value;
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
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
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
    public function getValue()
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
     * @param string|null        $value
     * @param \DateTimeInterface $updatedAt
     */
    public function update(string $value = null, \DateTimeInterface $updatedAt)
    {
        $this->value = $value;
        $this->updatedAt = $updatedAt;
    }
}
