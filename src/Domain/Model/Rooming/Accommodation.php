<?php

namespace Proximum\Vimeet\Domain\Model\Rooming;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Application\Command\Rooming\Accommodation\AccommodationOvernightCapacityView;
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

    /** @var ArrayCollection of AccommodationOvernightCapacity */
    private $overnightCapacities;

    /** @var Event */
    private $event;

    public function __construct(Event $event, string $title)
    {
        $this->title = $title;
        $this->event = $event;
        $this->overnightCapacities = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return AccommodationOvernightCapacity[]
     */
    public function getOvernightCapacities(): array
    {
        return $this->overnightCapacities->toArray();
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function addOvernightCapacity(AccommodationOvernightCapacity $accommodationOvernightCapacity): void
    {
        $this->overnightCapacities->add($accommodationOvernightCapacity);
    }

    /**
     * @param string                           $title
     * @param AccommodationOvernightCapacity[] $overnightCapacities
     */
    public function update(string $title, array $overnightCapacities): void
    {
        $this->title = $title;
        $this->overnightCapacities = new ArrayCollection($overnightCapacities);
    }
}
