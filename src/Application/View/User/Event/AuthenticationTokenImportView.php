<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\User\Event;

use Proximum\Vimeet\Domain\Model\Event;

class AuthenticationTokenImportView
{
    /** @var Event */
    public $event;

    /** @var string */
    public $email;

    /** @var string */
    public $token;

    public function __construct(Event $event, string $email, string $token)
    {
        $this->event = $event;
        $this->email = $email;
        $this->token = $token;
    }
}
