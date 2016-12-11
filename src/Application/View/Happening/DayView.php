<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Happening;

class DayView
{
    /**
     * @var int
     */
    private $scale;

    /**
     * @var \DateTimeInterface
     */
    public $startTime;

    /**
     * @var \DateTimeInterface
     */
    public $endTime;

    /**
     * @var array|HappeningView[]
     */
    public $happenings;

    /**
     * @param \DateTimeInterface $startTime
     * @param \DateTimeInterface $endTime
     * @param int                $scale
     * @param HappeningView[]    $happeningViews
     */
    public function __construct(
        \DateTimeInterface $startTime,
        \DateTimeInterface $endTime,
        $scale,
        array $happeningViews = []
    ) {
        $this->startTime  = $startTime;
        $this->endTime    = $endTime;
        $this->scale      = $scale;
        $this->happenings = $happeningViews;
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
