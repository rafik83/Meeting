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
     * @var int
     */
    public $id;

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
     * @var bool
     */
    public $active;

    /**
     * ScheduleSlotView constructor.
     *
     * @param int       $id
     * @param string    $title
     * @param \DateTime $begin
     * @param \DateTime $end
     * @param bool      $active
     */
    public function __construct($id, $title, \DateTime $begin, \DateTime $end, $active)
    {
        $this->id     = $id;
        $this->title  = $title;
        $this->begin  = $begin;
        $this->end    = $end;
        $this->active = $active;
    }
}
