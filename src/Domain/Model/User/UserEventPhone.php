<?php

namespace Proximum\Vimeet\Domain\Model\User;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class UserEventPhone
{
    /** @var int */
    private $id;

    /** @var User */
    private $user;

    /** @var Event */
    private $event;

    /** @var string */
    private $phone;

    /** @var string */
    private $code;

    /** @var DateTimeInterface */
    private $createdAt;

    /** @var bool */
    private $validated;

    /** @var DateTimeInterface|null */
    private $validatedAt;

    /** @var bool */
    private $stop;

    /**
     * @param User              $user
     * @param Event             $event
     * @param string            $code
     * @param string            $phone
     * @param DateTimeInterface $createdAt
     */
    public function __construct(User $user, Event $event, $code, $phone, DateTimeInterface $createdAt)
    {
        $this->user = $user;
        $this->event = $event;
        $this->code = $code;
        $this->phone = $phone;
        $this->createdAt = $createdAt;
        $this->validated = false;
        $this->validatedAt = null;
        $this->stop = false;
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
     * @return string
     */
    public function getCode()
    {
        return $this->code;
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
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @return DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return bool
     */
    public function isValidated()
    {
        return $this->validated;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getValidatedAt()
    {
        return $this->validatedAt;
    }

    /**
     * @param DateTimeInterface $dateTime
     */
    public function validate(\DateTimeInterface $dateTime)
    {
        $this->validatedAt = $dateTime;
        $this->validated = true;
    }

    /**
     * @return bool
     */
    public function isStop(): bool
    {
        return $this->stop;
    }
}
