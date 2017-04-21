<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Group;

use Proximum\Vimeet\Domain\Model\Event;

class UserViewQuery
{
    /** @var Event */
    public $event;

    /** @var string */
    public $email;

    /**
     * UserViewQuery constructor.
     *
     * @param Event  $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
