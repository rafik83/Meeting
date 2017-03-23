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
use Proximum\Vimeet\Domain\Model\Spot;

class SlotViewQuery
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var Event\Day
     */
    public $day;

    /**
     * @var Spot
     */
    public $spot;

    /**
     * SlotViewQuery constructor.
     *
     * @param Event     $event
     * @param Event\Day $day
     * @param Spot      $spot
     */
    public function __construct(Event $event, Event\Day $day, Spot $spot)
    {
        $this->event = $event;
        $this->day   = $day;
        $this->spot  = $spot;
    }
}
