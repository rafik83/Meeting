<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class Happening
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
     * @var Schedule
     */
    private $schedule;

    /**
     * @var \DateTime
     */
    private $begin;

    /**
     * @var \DateTime
     */
    private $end;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var bool
     */
    private $blocking;

    /**
     * Happening constructor.
     *
     * @param Event     $event
     * @param Schedule  $schedule
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param string    $title
     * @param string    $description
     * @param bool      $blocking
     */
    public function __construct(Event $event, Schedule $schedule, \DateTime $begin, \DateTime $end, $title, $description, $blocking)
    {
        $this->event       = $event;
        $this->schedule    = $schedule;
        $this->begin       = $begin;
        $this->end         = $end;
        $this->title       = $title;
        $this->description = $description;
        $this->blocking    = $blocking;
    }

    /**
     * Get id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param Event $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }

    /**
     * Get schedule.
     *
     * @return Schedule
     */
    public function getSchedule()
    {
        return $this->schedule;
    }

    /**
     * Get begin.
     *
     * @return \DateTime
     */
    public function getBegin()
    {
        return $this->begin;
    }

    /**
     * Get end.
     *
     * @return \DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get blocking.
     *
     * @return bool
     */
    public function getBlocking()
    {
        return $this->blocking;
    }
}
