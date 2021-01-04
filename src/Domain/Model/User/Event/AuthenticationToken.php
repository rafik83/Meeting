<?php

namespace Proximum\Vimeet\Domain\Model\User\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class AuthenticationToken
{
    /** @var int */
    private $id;

    /** @var User */
    private $user;

    /** @var Event */
    private $event;

    /** @var string */
    private $token;

    /** @var null|\DateTimeInterface */
    private $expiredAt;

    /** @var \DateTimeInterface */
    private $createdAt;

    public function __construct(
        User $user,
        Event $event,
        string $token,
        \DateTimeInterface $createdAt,
        ?\DateTimeInterface $expiredAt = null
    ) {
        $this->user = $user;
        $this->event = $event;
        $this->token = $token;
        $this->createdAt = $createdAt;
        $this->expiredAt = $expiredAt;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getExpiredAt(): ?\DateTimeInterface
    {
        return $this->expiredAt;
    }

    public function updateTokenAndExpiredAt(string $token, ?\DateTimeInterface $expiredAt): void
    {
        $this->token = $token;
        $this->expiredAt = $expiredAt;
    }
}
