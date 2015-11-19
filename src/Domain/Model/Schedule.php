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

class Schedule
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var ArrayCollection
     */
    private $meetingSlots;

    /**
     * Schedule constructor.
     *
     * @param Event     $event
     * @param \DateTime $date
     */
    public function __construct(Event $event, \DateTime $date)
    {
        $this->event        = $event;
        $this->date         = $date;
        $this->meetingSlots = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get event
     *
     * @return mixed
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Get meetingSlots
     *
     * @return ArrayCollection
     */
    public function getMeetingSlots()
    {
        return $this->meetingSlots;
    }
}
