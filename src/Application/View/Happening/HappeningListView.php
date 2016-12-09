<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Happening;

use Proximum\Vimeet\Domain\Model\Event;

class HappeningListView
{
    /**
     * @var Event
     */
    private $event;

    /**
     * @var \DateTimeInterface
     */
    private $startTime;

    /**
     * @var \DateTimeInterface
     */
    private $endTime;

    /**
     * @var int
     */
    private $scale;

    /**
     * @var HappeningView[]
     */
    private $morning;

    /**
     * @var HappeningView[]
     */
    private $afternoon;

    /**
     * @var \DateTimeInterface
     */
    private $middleTime;

    /**
     * HappeningListView constructor.
     *
     * @param Event              $event
     * @param \DateTimeInterface $startTime
     * @param \DateTimeInterface $endTime
     * @param \DateTimeInterface $middleTime
     * @param int                $scale
     * @param HappeningView[]    $morning
     * @param HappeningView[]    $afternoon
     */
    public function __construct(
        Event $event,
        \DateTimeInterface $startTime,
        \DateTimeInterface $endTime,
        \DateTimeInterface $middleTime,
        $scale,
        array $morning,
        array $afternoon
    ) {
        $this->event      = $event;
        $this->morning    = $morning;
        $this->afternoon  = $afternoon;
        $this->startTime  = $startTime;
        $this->endTime    = $endTime;
        $this->scale      = $scale;
        $this->middleTime = $middleTime;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return HappeningView[]
     */
    public function getMorning()
    {
        return $this->morning;
    }

    /**
     * @return HappeningView[]
     */
    public function getAfternoon()
    {
        return $this->afternoon;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getMiddleTime()
    {
        return $this->middleTime;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDay()
    {
        return $this->startTime;
    }

    /**
     * @return string
     */
    public function getScale()
    {
        return gmdate('H:i', $this->scale * 60);
    }
}
