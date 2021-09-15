<?php

namespace Proximum\Vimeet\Domain\Model\Rooming;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class Stay
{
    public const ROOM_TYPE_SINGLE = 'single';
    public const ROOM_TYPE_DOUBLE = 'double';
    public const ROOM_TYPE_TWIN = 'twin';

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
    private $roomNumber;

    /** @var string */
    private $roomType;

    /** @var ArrayCollection of User */
    private $users;

    /** @var User */
    private $user;

    public function __construct(
        Event $event,
        User $user,
        \DateTimeInterface $arrival,
        \DateTimeInterface $departure,
        Accommodation $accommodation,
        string $roomType,
        string $roomNumber
    ) {
        $this->event = $event;
        $this->arrival = $arrival;
        $this->departure = $departure;
        $this->accommodation = $accommodation;
        $this->roomType = $roomType;
        $this->users = new ArrayCollection([$user]);
        $this->roomNumber = $roomNumber;
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

    public function getRoomNumber(): string
    {
        return $this->roomNumber;
    }

    public function setRoomNumber(string $roomNumber): void
    {
        $this->roomNumber = $roomNumber;
    }

    /**
     * @return User[]
     */
    public function getUsers(): array
    {
        return $this->users->toArray();
    }

    public function addUser(User $user): void
    {
        $this->users->add($user);
    }
}
