<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Planner;

use Proximum\Vimeet\Domain\Model\Event;

class ExportJobCreator
{
    /** @var Event */
    public $event;

    /** @var string */
    public $locale;

    /**
     * ExportJobCreator constructor.
     *
     * @param Event  $event
     * @param string $locale
     */
    public function __construct(Event $event, $locale)
    {
        $this->event  = $event;
        $this->locale = $locale;
    }
}
