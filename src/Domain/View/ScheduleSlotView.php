<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View;

class ScheduleSlotView
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var \DateTime
     */
    public $begin;

    /**
     * @var \DateTime
     */
    public $end;

    /**
     * ScheduleSlotView constructor.
     *
     * @param string    $title
     * @param \DateTime $begin
     * @param \DateTime $end
     */
    public function __construct($title, \DateTime $begin, \DateTime $end)
    {
        $this->title = $title;
        $this->begin = $begin;
        $this->end   = $end;
    }
}
