<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Rooming\Stay;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class AddComment
{
    /** @var Event */
    public $event;

    /** @var User */
    public $user;

    /** @var string */
    public $comment;

    public function __construct(Event $event, User $user, string $comment)
    {
        $this->event   = $event;
        $this->user    = $user;
        $this->comment = $comment;
    }
}
