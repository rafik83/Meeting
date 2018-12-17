<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\User\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class PresenceDate
{
    /** @var null|int */
    private $id;

    /** @var User */
    private $user;

    /** @var Event */
    private $event;

    /** @var \DateTimeInterface */
    private $arrival;

    /** @var \DateTimeInterface */
    private $departure;

    public function __construct(
        User $user,
        Event $event,
        \DateTimeInterface $arrival,
        \DateTimeInterface $departure
    ) {
        $this->user = $user;
        $this->event = $event;
        $this->arrival = $arrival;
        $this->departure = $departure;
    }

    /**
     * @return null|int
     */
    public function getId(): ?int
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
     * @return \DateTimeInterface
     */
    public function getArrival(): \DateTimeInterface
    {
        return $this->arrival;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDeparture(): \DateTimeInterface
    {
        return $this->departure;
    }
}
