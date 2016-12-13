<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda;

class AgendaView
{
    /**
     * @var DayView[]
     */
    public $days;

    /**
     * @param array $dayViews
     */
    public function __construct(array $dayViews)
    {
        $this->days = $dayViews;
    }
}
