<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\Spot;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;

class SpotSatisfactionListQuery
{
    /** @var Event */
    public $event;

    /** @var MeetingSlot[] */
    public $slots;

    /**
     * @param Event         $event
     * @param MeetingSlot[] $slots
     */
    public function __construct(Event $event, array $slots)
    {
        $this->event = $event;
        $this->slots = $slots;
    }
}
