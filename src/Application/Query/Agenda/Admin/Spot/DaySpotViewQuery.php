<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda\Admin\Spot;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\Spot;

class DaySpotViewQuery
{
    /**
     * @var Day
     */
    public $day;

    /**
     * @var int
     */
    public $dayNumber;

    /**
     * @var Event
     */
    public $event;

    /**
     * @var Spot
     */
    public $spot;

    /**
     * DaySpotViewQuery constructor.
     *
     * @param Day   $day
     * @param int   $dayNumber
     * @param Event $event
     * @param Spot  $spot
     */
    public function __construct(Day $day, $dayNumber, Event $event, Spot $spot)
    {
        $this->day       = $day;
        $this->dayNumber = $dayNumber;
        $this->event     = $event;
        $this->spot      = $spot;
    }
}
