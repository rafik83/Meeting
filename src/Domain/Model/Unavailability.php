<?php

namespace Proximum\Vimeet\Domain\Model;

use Proximum\Vimeet\Domain\Time\TimeRangeInterface;

/**
 * User's Unavailability for an Event
 */
class Unavailability implements TimeRangeInterface
{
    const CREATED_BY_USER = 'user';
    const CREATED_BY_SYSTEM = 'system';
    const CREATED_BY_VALUES = [self::CREATED_BY_USER, self::CREATED_BY_SYSTEM];

    /**
     * @var int
     */
    private $id;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var \DateTimeInterface
     */
    private $begin;

    /**
     * @var \DateTimeInterface
     */
    private $end;

    /**
     * @var string|null
     */
    private $message;

    /** @var string one of self::CREATED_BY_VALUES */
    private $createdBy;

    /**
     * @param User               $user
     * @param Event              $event
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     * @param string|null        $message
     * @param string             $createdBy one of self::CREATED_BY_VALUES
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        User $user,
        Event $event,
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        $message = null,
        $createdBy = self::CREATED_BY_USER
    ) {
        $this->user    = $user;
        $this->event   = $event;
        $this->begin   = $begin;
        $this->end     = $end;
        $this->message = $message;

        if (!\in_array($createdBy, self::CREATED_BY_VALUES, true)) {
            throw new \InvalidArgumentException('Unavailability\'s createdBy is invalid');
        }

        $this->createdBy = $createdBy;
    }

    /**
     * Get id.
     *
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
     * Get begin.
     *
     * @return \DateTimeInterface
     */
    public function getBegin()
    {
        return $this->begin;
    }

    /**
     * Get end.
     *
     * @return \DateTimeInterface
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @return string|null
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param Unavailability $unavailability
     */
    public function merge(Unavailability $unavailability)
    {
        if ($unavailability->getBegin() < $this->getBegin()) {
            $this->begin = $unavailability->getBegin();
        }

        if ($unavailability->getEnd() > $this->getEnd()) {
            $this->end = $unavailability->getEnd();
        }
    }

    /**
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     */
    public function update(\DateTimeInterface $begin, \DateTimeInterface $end)
    {
        $this->begin = $begin;
        $this->end   = $end;
    }

    /**
     * @return bool
     */
    public function isDeletableByUser(): bool
    {
        return $this->isCreatedByUser();
    }

    /**
     * @return bool
     */
    public function isCreatedByUser(): bool
    {
        return self::CREATED_BY_USER === $this->createdBy;
    }
}
