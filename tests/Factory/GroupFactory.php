<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Factory;

use DateTime;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Model\User;

class GroupFactory
{
    /**
     * @param Event|null    $event
     * @param User|null     $user
     * @param DateTime|null $dateTime
     * @param string|null   $title
     *
     * @return Group
     */
    public static function createGroup(
        Event $event = null,
        User $user = null,
        DateTime $dateTime = null,
        $title = null
    ) {
        $event    = ($event !== null) ? $event : EventFactory::createEvent();
        $user     = ($user !== null) ? $user : UserFactory::create();
        $dateTime = ($dateTime !== null) ? $dateTime : new DateTime();
        $title    = ($title !== null) ? $title : 'GroupTitle';

        return new Group($event, $user, $title, $dateTime);
    }
}
