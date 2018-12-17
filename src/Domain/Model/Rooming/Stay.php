<?php

namespace Proximum\Vimeet\Domain\Model\Rooming;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class Stay
{
    /** @var int|null */
    private $id;

    /** @var Event */
    private $event;

    /** @var \DateTimeInterface */
    private $arrival;

    /** @var \DateTimeInterface */
    private $departure;

    /** @var Accommodation */
    private $accommodation;

    /** @var string */
    private $roomType;

    /** @var ArrayCollection of User */
    private $users;

    public function __construct(
        Event $event,
        \DateTimeInterface $arrival,
        \DateTimeInterface $departure,
        Accommodation $accommodation,
        string $roomType
    ) {
        $this->event = $event;
        $this->arrival = $arrival;
        $this->departure = $departure;
        $this->accommodation = $accommodation;
        $this->roomType = $roomType;
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getArrival(): \DateTimeInterface
    {
        return $this->arrival;
    }

    public function getDeparture(): \DateTimeInterface
    {
        return $this->departure;
    }

    public function getAccommodation(): Accommodation
    {
        return $this->accommodation;
    }

    public function getRoomType(): string
    {
        return $this->roomType;
    }

    /**
     * @return User[]
     */
    public function getUsers(): array
    {
        return $this->users->toArray();
    }
}
