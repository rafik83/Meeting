<?php

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

    /** @var null|\DateTimeInterface */
    private $arrival;

    /** @var null|\DateTimeInterface */
    private $departure;

    /** @var bool */
    private $hasArrivalHours;

    /** @var bool */
    private $hasDepartureHours;

    public function __construct(
        User $user,
        Event $event,
        ?\DateTimeInterface $arrival,
        ?\DateTimeInterface $departure,
        bool $hasArrivalHours,
        bool $hasDepartureHours
    ) {
        $this->user = $user;
        $this->event = $event;
        $this->arrival = $arrival;
        $this->departure = $departure;
        $this->hasArrivalHours = $hasArrivalHours;
        $this->hasDepartureHours = $hasDepartureHours;
    }

    public function getId(): ?int
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

    public function getArrival(): ?\DateTimeInterface
    {
        return $this->arrival;
    }

    public function getDeparture(): ?\DateTimeInterface
    {
        return $this->departure;
    }

    public function hasDepartureHours(): bool
    {
        return $this->hasDepartureHours;
    }

    public function hasArrivalHours(): bool
    {
        return $this->hasArrivalHours;
    }
}
