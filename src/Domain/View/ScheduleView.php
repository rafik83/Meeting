<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View;

class ScheduleView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var \DateTime
     */
    public $date;

    /**
     * @var ScheduleSlotView[]
     */
    public $slots;

    /**
     * ScheduleView constructor.
     *
     * @param int       $id
     * @param \DateTime $date
     * @param array     $slots
     */
    public function __construct($id, \DateTime $date, array $slots)
    {
        $this->id = $id;
        $this->date = $date;
        $this->slots = $slots;
    }
}
