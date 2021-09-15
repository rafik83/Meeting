<?php

namespace Proximum\Vimeet\Domain\Model\User\Agenda;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class Version
{
    /** @var int */
    private $id;

    /** @var Event */
    private $event;

    /** @var User */
    private $user;

    /** @var array */
    private $version;

    /** @var \DateTimeInterface */
    private $createdAt;

    /**
     * @param Event              $event
     * @param User               $user
     * @param array              $version
     * @param \DateTimeInterface $createdAt
     */
    public function __construct(Event $event, User $user, array $version, \DateTimeInterface $createdAt)
    {
        $this->event = $event;
        $this->user = $user;
        $this->version = $version;
        $this->createdAt = $createdAt;
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
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return array
     */
    public function getVersion(): array
    {
        return $this->version;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }
}
