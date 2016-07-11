<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class MenuViewQuery
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
     * @var string
     */
    public $locale;

    /**
     * MenuViewQuery constructor.
     *
     * @param Event $event
     * @param User  $user
     * @param       $locale
     */
    public function __construct(Event $event, User $user, $locale)
    {
        $this->event  = $event;
        $this->user   = $user;
        $this->locale = $locale;
    }
}
