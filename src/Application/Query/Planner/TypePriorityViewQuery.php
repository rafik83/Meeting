<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Planner;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Application\View\Planner\TypeView;

class TypePriorityViewQuery
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var TypeView[]
     */
    public $types;

    /**
     * @param Event     $event
     * @param TypeView[] $types
     */
    public function __construct(Event $event, array $types)
    {
        $this->event = $event;
        $this->types = $types;
    }
}
