<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Domain\Model\User;

interface AuthenticationManagerInterface
{
    /**
     * Authenticate the user
     *
     * @param User $user
     */
    public function authenticate(User $user);

    /**
     * Disconnect the user
     */
    public function disconnect();
}
