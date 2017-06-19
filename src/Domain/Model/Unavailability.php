<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use Proximum\Vimeet\Domain\Time\TimeRangeInterface;

/**
 * User's Unavailability for an Event
 */
class Unavailability implements TimeRangeInterface
{
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

    /**
     * @param User               $user
     * @param Event              $event
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     * @param string|null        $message
     */
    public function __construct(
        User $user,
        Event $event,
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        $message = null
    ) {
        $this->user    = $user;
        $this->event   = $event;
        $this->begin   = $begin;
        $this->end     = $end;
        $this->message = $message;
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
}
