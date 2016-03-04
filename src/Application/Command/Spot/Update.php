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

class Update
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $property;

    /**
     * @var mixed
     */
    public $value;

    /**
     * Update constructor.
     *
     * @param Event  $event
     * @param int    $id
     * @param string $property
     * @param mixed  $value
     */
    public function __construct(Event $event, $id, $property, $value)
    {
        $this->event    = $event;
        $this->id       = $id;
        $this->property = $property;
        $this->value    = $value;
    }
}
