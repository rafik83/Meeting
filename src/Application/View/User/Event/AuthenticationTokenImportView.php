<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\User\Event;

class AuthenticationTokenImportView
{
    /** @var string */
    public $email;

    /** @var string */
    public $token;

    public function __construct(string $email, string $token)
    {
        $this->email = $email;
        $this->token = $token;
    }
}
