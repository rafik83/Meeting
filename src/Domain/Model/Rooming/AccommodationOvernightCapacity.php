<?php

namespace Proximum\Vimeet\Domain\Model\Rooming;

/**
 * "Capacité d'un lieu d'hébergement par nuitée"
 */
class AccommodationOvernightCapacity
{
    /** @var int */
    private $id;

    /** @var \DateTimeInterface */
    private $date;

    /** @var int */
    private $capacity;

    /** @var Accommodation */
    private $accommodation;

    public function __construct(
        Accommodation $accommodation,
        \DateTimeInterface $date,
        int $capacity
    ) {
        $this->accommodation = $accommodation;
        $this->date = $date;
        $this->capacity = $capacity;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function getCapacity(): int
    {
        return $this->capacity;
    }

    public function getAccommodation(): Accommodation
    {
        return $this->accommodation;
    }
}
