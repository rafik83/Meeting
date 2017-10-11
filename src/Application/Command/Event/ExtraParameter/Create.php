<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event\ExtraParameter;

use Proximum\Vimeet\Domain\Model\Event;

class Create
{
    /** @var Event */
    public $event;

    /** @var string */
    public $type;

    /** @var string */
    public $name;

    /** @var string */
    public $value;

    /**
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
