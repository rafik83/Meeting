<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Domain\Model\AbstractUser;

interface AuthenticationManagerInterface
{
    /**
     * Authenticate the user
     *
     * @param AbstractUser $user
     * @param string       $providerKey
     */
    public function authenticate(AbstractUser $user, $providerKey);

    /**
     * Disconnect the user
     */
    public function disconnect();
}
