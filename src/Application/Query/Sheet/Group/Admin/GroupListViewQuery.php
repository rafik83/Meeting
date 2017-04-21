<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet\Group\Admin;

use Proximum\Vimeet\Domain\Model\Event;

class GroupListViewQuery
{
    /** @var Event */
    public $event;

    /**
     * GroupListViewQuery constructor.
     *
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
