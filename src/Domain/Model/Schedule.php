<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

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
     * Schedule constructor.
     *
     * @param Event     $event
     * @param \DateTime $date
     */
    public function __construct(Event $event, \DateTime $date)
    {
        $this->event = $event;
        $this->date  = $date;
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
}
