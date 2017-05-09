<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event\Sheet;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\EventDispatcher;

class SheetGroupCreatedEvent extends EventDispatcher\Event
{
    /** @var Event */
    private $event;

    /** @var User */
    private $user;

    /** @var Group */
    private $group;

    /**
     * SheetGroupCreatedEvent constructor.
     *
     * @param Event $event
     * @param User  $user
     * @param Group $group
     */
    public function __construct(Event $event, User $user, Group $group)
    {
        $this->event = $event;
        $this->user  = $user;
        $this->group = $group;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }
}
