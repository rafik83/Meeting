<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\StaticFormulation;

use Proximum\Vimeet\Domain\Model\Event;

class StaticFormulationListViewQuery
{
    /** @var Event */
    public $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
