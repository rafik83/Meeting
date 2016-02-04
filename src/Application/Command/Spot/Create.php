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

class Create
{
    /**
     * @var string
     */
    public $reference;

    /**
     * @var Event
     */
    public $event;

    /**
     * @var int
     */
    public $size;

    /**
     * @var int
     */
    public $meetingCapacity;

    /**
     * @var int
     */
    public $seatCapacity;

    /**
     * @var boolean
     */
    public $active;

    /**
     * Create constructor.
     *
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
