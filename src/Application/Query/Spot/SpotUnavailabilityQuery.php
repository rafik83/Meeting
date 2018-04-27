<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Spot;

use Proximum\Vimeet\Domain\Model\Event;

class SpotUnavailabilityQuery
{
    /**
     * @var Event
     */
    public $event;

    /**
     * Array of spot ids
     *
     * @var array
     */
    public $spots;

    /**
     * SpotUnavailabilityQuery constructor.
     *
     * @param Event $event
     * @param array $spots
     */
    public function __construct(Event $event, array $spots)
    {
        $this->event = $event;
        $this->spots = $spots;
    }
}
