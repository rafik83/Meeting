<?php

namespace Proximum\Vimeet\Domain\Model\Rooming;

use Proximum\Vimeet\Domain\Model\Event;

/**
 * "Lieu d'hébergement"
 */
class Accommodation
{
    /** @var int */
    private $id;

    /** @var string */
    private $title;

    /** @var AccommodationOvernightCapacity[] */
    private $overnightCapacities;

    /** @var Event */
    private $event;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getOvernightCapacities(): array
    {
        return $this->overnightCapacities;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }
}
