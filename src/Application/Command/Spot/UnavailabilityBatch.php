<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Spot;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;

class UnavailabilityBatch
{
    /**
     * @var Event
     */
    private $event;

    /**
     * Array of Spot ids
     *
     * @var array
     */
    private $spotIds;

    /**
     * @var MeetingSlot[]
     */
    public $meetingSlots;

    /**
     * UnavailabilityBatch constructor.
     *
     * @param array $ids "Array of Spot ids"
     * @param Event $event
     */
    public function __construct(array $ids, Event $event)
    {
        $this->spotIds = $ids;
        $this->event   = $event;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Array of Spot ids
     *
     * @return array
     */
    public function getSpotIds()
    {
        return $this->spotIds;
    }
}
