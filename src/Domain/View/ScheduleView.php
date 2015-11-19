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
     * @param \DateTime $date
     * @param array     $slots
     */
    public function __construct(\DateTime $date, array $slots)
    {
        $this->date  = $date;
        $this->slots = $slots;
    }
}
