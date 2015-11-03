<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event;

use Proximum\Vimeet\Domain\Model\Event;

class Update
{
    public $event;

    public $locales;

    public $fallback;

    public function __construct(Event $event)
    {
        $this->event    = $event;
        $this->locales  = $event->getLocales();
        $this->fallback = $event->getFallback();
    }
}
