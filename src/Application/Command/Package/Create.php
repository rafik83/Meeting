<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Package;

use Proximum\Vimeet\Domain\Model\Event;

class Create
{
    /** @var string */
    public $title;

    /** @var Event */
    public $event;

    public function __construct(Event $event = null)
    {
        $this->event = $event;
    }
}
