<?php

namespace Proximum\Vimeet\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Application\Exception\Spot\PropertyNotSupportedException;

/**
 * "Lieu".
 */
class Spot
{
    const PRIORITY_ASSIGN    = 8;
    const PRIORITY_MUTUALIZE = 12;
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $reference;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var float
     */
    private $size;

    /**
     * @var int
     */
    private $meetingCapacity;

    /**
     * @var int
     */
    private $seatCapacity;

    /**
     * @var bool
     */
    private $active;

    /**
     * @var int
     */
    private $priority = 12;

    /**
     * @var bool
     */
    private $visio;

    /**
     * @var ArrayCollection
     */
    private $sheets;

    /**
     * @var ArrayCollection
     */
    private $spotUnavailabilities = [];

    /**
     * Spot constructor.
     *
     * @param string $reference
     * @param Event  $event
     * @param float  $size
     * @param int    $meetingCapacity
     * @param int    $seatCapacity
     * @param bool   $active
     * @param int    $priority
     * @param bool   $visio
     */
    public function __construct(
        $reference,
        Event $event,
        $size,
        $meetingCapacity,
        $seatCapacity,
        $active,
        $priority = self::PRIORITY_MUTUALIZE,
        $visio = false
    ) {
        $this->reference            = $reference;
        $this->event                = $event;
        $this->size                 = $size;
        $this->meetingCapacity      = $meetingCapacity;
        $this->seatCapacity         = $seatCapacity;
        $this->active               = $active;
        $this->sheets               = new ArrayCollection();
        $this->spotUnavailabilities = new ArrayCollection();
        $this->priority             = $priority;
        $this->visio                = $visio;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get reference
     *
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Get event
     *
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Get size
     *
     * @return float
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Get meetingCapacity
     *
     * @return int
     */
    public function getMeetingCapacity()
    {
        return $this->meetingCapacity;
    }

    /**
     * Get seatCapacity
     *
     * @return int
     */
    public function getSeatCapacity()
    {
        return $this->seatCapacity;
    }

    /**
     * Get active
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param string $property
     * @param int    $value
     *
     * @throws PropertyNotSupportedException
     *
     * @return Spot
     */
    public function update($property, $value)
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }

        return $this;
    }

    /**
     * @param $property
     *
     * @return mixed
     */
    public function value($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }

        return null;
    }

    /**
     * @return bool
     */
    public function hasSheets()
    {
        return !$this->sheets->isEmpty();
    }

    /**
     * @return int
     */
    public function countSheets()
    {
        return $this->sheets->count();
    }

    /**
     * @param Sheet $sheet
     */
    public function addSheet(Sheet $sheet)
    {
        $this->sheets->add($sheet);
    }

    /**
     * @param Sheet $sheet
     */
    public function removeSheet(Sheet $sheet)
    {
        $this->sheets->removeElement($sheet);
    }

    /**
     * @return Sheet[]
     */
    public function getSheets()
    {
        return $this->sheets->toArray();
    }

    /**
     * @return SpotUnavailability[]
     */
    public function getSpotUnavailabilities()
    {
        return $this->spotUnavailabilities->toArray();
    }

    /**
     * @param SpotUnavailability[] $spotUnavailabilities
     */
    public function addSpotUnavailability(array $spotUnavailabilities = [])
    {
        $this->spotUnavailabilities = new ArrayCollection($spotUnavailabilities);
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     *
     * @return self
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasUnavailability()
    {
        return $this->spotUnavailabilities->count() > 0;
    }

    /**
     * @return bool
     */
    public function isVisio()
    {
        return $this->visio;
    }

    /**
     * Set visio to false
     *
     * @return self
     */
    public function unVisio()
    {
        $this->visio = false;

        return $this;
    }

    /**
     * Set visio to true
     *
     * @return self
     */
    public function goToVisio()
    {
        $this->visio = true;

        return $this;
    }
}
