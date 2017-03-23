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

class AgendaSpotViewQuery
{
    /**
     * @var Spot
     */
    public $spot;

    /**
     * @var Event
     */
    public $event;

    /**
     * AgendaSpotViewQuery constructor.
     *
     * @param Spot  $spot
     * @param Event $event
     */
    public function __construct(Spot $spot, Event $event)
    {
        $this->spot  = $spot;
        $this->event = $event;
    }
}
