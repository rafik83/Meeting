<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Schedule;

class ScheduleSlotView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    /**
     * @var \DateTimeInterface
     */
    public $begin;

    /**
     * @var \DateTimeInterface
     */
    public $end;

    /**
     * @var bool
     */
    public $active;

    /**
     * ScheduleSlotView constructor.
     *
     * @param int                $id
     * @param string             $title
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     * @param bool               $active
     */
    public function __construct($id, $title, \DateTimeInterface $begin, \DateTimeInterface $end, $active)
    {
        $this->id     = $id;
        $this->title  = $title;
        $this->begin  = $begin;
        $this->end    = $end;
        $this->active = $active;
    }
}
