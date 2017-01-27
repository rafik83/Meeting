<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Spot;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Event;

class UnavailabilityBatch
{
    /**
     * @var Event
     */
    private $event;

    /**
     * @var array
     */
    private $ids;

    /**
     * @var ArrayCollection
     */
    public $spot;

    /**
     * UnavailabilityBatch constructor.
     *
     * @param array $ids
     * @param Event $event
     */
    public function __construct(array $ids, Event $event)
    {
        $this->ids   = $ids;
        $this->event = $event;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return array
     */
    public function getIds()
    {
        return $this->ids;
    }
}
