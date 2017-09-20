<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Meeting;

class MeetingDdayView
{
    /**
     * @var \DateTimeInterface
     */
    private $datetime;

    /**
     * @var string
     */
    public $spotName;

    /**
     * MeetingDdayView constructor.
     *
     * @param \DateTimeInterface $datetime
     * @param string             $spotName
     */
    public function __construct(\DateTimeInterface $datetime, string $spotName)
    {
        $this->datetime = $datetime;
        $this->spotName = $spotName;
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->datetime->format('d/m/y');
    }

    /**
     * @return string
     */
    public function getTime(): string
    {
        return $this->datetime->format('h:i');
    }
}
