<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

/**
 * "Stand".
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
     * @var int
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
     * @var boolean
     */
    private $active;

    /**
     * Spot constructor.
     *
     * @param string $reference
     * @param Event  $event
     * @param int    $size
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
     * @return int
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
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }
}
