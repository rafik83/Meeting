<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\MeetingSlot;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Meeting\Slot\Recipe;

class Generate
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var Recipe[]
     */
    public $recipes = [];

    /**
     * Generate constructor.
     *
     * @param $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
