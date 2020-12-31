<?php

namespace Proximum\Vimeet\Domain\Model\Token;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Token\UserEventTokenType;

class UserEventToken
{
    /** @var int */
    private $id;

    /** @var User */
    private $user;

    /** @var Event */
    private $event;

    /** @var string */
    private $token;

    /** @var string */
    private $type;

    /** @var \DateTimeInterface */
    private $createdAt;

    /** @var bool */
    private $confirmed;

    /** @var \DateTimeInterface|null */
    private $confirmedAt;

    /**
     * @param Event              $event
     * @param User               $user
     * @param string             $type
     * @param string             $token
     * @param \DateTimeInterface $createdAt
     */
    public function __construct(Event $event, User $user, $type, $token, \DateTimeInterface $createdAt)
    {
        $this->event = $event;
        $this->user = $user;
        $this->type = $type;
        $this->token = $token;
        $this->createdAt = $createdAt;
        $this->confirmed = false;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return bool
     */
    public function isConfirmed()
    {
        return $this->confirmed;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getConfirmedAt()
    {
        return $this->confirmedAt;
    }

    /**
     * @param \DateTimeInterface $confirmedAt
     */
    public function confirm(\DateTimeInterface $confirmedAt)
    {
        $this->confirmed   = true;
        $this->confirmedAt = $confirmedAt;
    }

    public function unConfirm(): void
    {
        $this->confirmed = false;
        $this->confirmedAt = null;
    }

    /**
     * @return bool
     */
    public function isAgendaConfirmation(): bool
    {
        return UserEventTokenType::AGENDA_CONFIRMATION === $this->type;
    }
}
