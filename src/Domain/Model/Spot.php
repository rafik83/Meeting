<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Application\Exception\Spot\PropertyNotSupportedException;

/**
 * "Lieu".
 */
class Spot
{
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
     * @var ArrayCollection
     */
    private $sheets;

    /**
     * Spot constructor.
     *
     * @param string $reference
     * @param Event  $event
     * @param float  $size
     * @param int    $meetingCapacity
     * @param int    $seatCapacity
     * @param bool   $active
     */
    public function __construct(
        $reference,
        Event $event,
        $size,
        $meetingCapacity,
        $seatCapacity,
        $active
    ) {
        $this->reference       = $reference;
        $this->event           = $event;
        $this->size            = $size;
        $this->meetingCapacity = $meetingCapacity;
        $this->seatCapacity    = $seatCapacity;
        $this->active          = $active;
        $this->sheets          = new ArrayCollection();
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
     * @return Spot
     * @throws PropertyNotSupportedException
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
     * @return Sheet[]
     */
    public function getSheets()
    {
        return $this->sheets->toArray();
    }
}
