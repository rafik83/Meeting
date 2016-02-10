<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Spot;

use Proximum\Vimeet\Domain\Model\Event;

class EnableBatch
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var array
     */
    public $idsSpot;

    /**
     * DeleteBatch constructor.
     *
     * @param       $idsSpot
     * @param Event $event
     */
    public function __construct($idsSpot, Event $event)
    {
        $this->idsSpot = $idsSpot;
        $this->event   = $event;
    }

}
