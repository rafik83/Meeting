<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Group;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\Group;

class DuplicateToEvent implements Command
{
    /** @var Group */
    public $group;

    /** @var Event */
    public $toEvent;

    /**
     * @param Group $group
     * @param Event $toEvent
     */
    public function __construct(Group $group, Event $toEvent)
    {
        $this->group = $group;
        $this->toEvent = $toEvent;
    }
}
