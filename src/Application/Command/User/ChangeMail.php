<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\User;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class ChangeMail
{
    /** @var User */
    public $user;

    /** @var Event */
    public $event;

    /** @var string */
    public $mail;

    /** @var string */
    public $password;

    public function __construct(User $user, Event $event)
    {
        $this->user  = $user;
        $this->event = $event;
    }
}
