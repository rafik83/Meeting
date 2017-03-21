<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Planner;

use Proximum\Vimeet\Domain\Model\Event;

class Export
{
    /** @var bool */
    public $lockMeetingRequest = false;

    /** @var string */
    public $solutionType;

    /** @var Event */
    public $event;

    /**
     * @param $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
