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
    private $happenings;

    /**
     * HappeningListView constructor.
     *
     * @param Event              $event
     * @param \DateTimeInterface $startTime
     * @param \DateTimeInterface $endTime
     * @param int                $scale
     * @param HappeningView[]    $happenings
     */
    public function __construct(
        Event $event,
        \DateTimeInterface $startTime,
        \DateTimeInterface $endTime,
        $scale,
        array $happenings
    ) {
        $this->event      = $event;
        $this->startTime  = $startTime;
        $this->endTime    = $endTime;
        $this->scale      = $scale;
        $this->happenings = $happenings;
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
    public function getHappenings()
    {
        return $this->happenings;
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
