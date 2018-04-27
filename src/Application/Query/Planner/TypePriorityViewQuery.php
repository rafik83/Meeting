<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Planner;

use Proximum\Vimeet\Application\View\Planner\TypeView;
use Proximum\Vimeet\Domain\Model\Event;

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
     * @param Event      $event
     * @param TypeView[] $types
     */
    public function __construct(Event $event, array $types)
    {
        $this->event = $event;
        $this->types = $types;
    }
}
