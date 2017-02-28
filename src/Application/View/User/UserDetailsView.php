<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\User;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class UserDetailsView
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var User
     */
    public $user;

    /**
     * @var array
     */
    public $events;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * UserDetailsView constructor.
     *
     * @param Event $event
     * @param User  $user
     * @param Sheet $sheet
     * @param array $events
     */
    public function __construct(Event $event, User $user, Sheet $sheet, array $events)
    {
        $this->event  = $event;
        $this->user   = $user;
        $this->events = $events;
        $this->sheet  = $sheet;
    }
}
