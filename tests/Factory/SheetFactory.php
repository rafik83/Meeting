<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Factory;

use DateTime;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;

class SheetFactory
{
    /**
     * @param Event    $event
     * @param User     $user
     *
     * @param DateTime $datetime
     *
     * @return Sheet
     */
    public static function create(Event $event = null, User $user = null, DateTime $datetime = null)
    {
        $event = ($event !== null) ? $event : EventFactory::createEvent();

        $type     = new Type($event);
        $user     = ($user !== null) ? $user : new User('user@vimeet.com', 'salt', 'password', 'fr');
        $datetime = ($datetime !== null) ? $datetime : new DateTime();

        $sheet = new Sheet(
            $event,
            $type,
            [],
            $user,
            $datetime
        );

        return $sheet;
    }
}
