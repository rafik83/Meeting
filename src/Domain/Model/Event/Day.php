<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Event;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Model\Event;

class Day
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
     * @var DateTimeInterface
     */
    private $day;

    /**
     * @var DateTimeInterface
     */
    private $startTime;

    /**
     * @var DateTimeInterface
     */
    private $endTime;

    /**
     * @param Event             $event
     * @param DateTimeInterface $day
     * @param DateTimeInterface $startTime
     * @param DateTimeInterface $endTime
     */
    public function __construct(
        Event $event,
        DateTimeInterface $day,
        DateTimeInterface $startTime,
        DateTimeInterface $endTime
    ) {
        $this->event     = $event;
        $this->day       = $day;
        $this->startTime = $startTime;
        $this->endTime   = $endTime;
    }

    /**
     * @return int
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
     * @return DateTimeInterface
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * @return DateTimeInterface
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * @return DateTimeInterface
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * @param DateTimeInterface $day
     */
    public function setDay($day)
    {
        $this->day = $day;
    }

    /**
     * @param DateTimeInterface $startTime
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;
    }

    /**
     * @param DateTimeInterface $endTime
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;
    }
}
