<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Factory;

use Proximum\Vimeet\Domain\Model\User;

class UserFactory
{
    /**
     * @param string $email
     *
     * @return User
     */
    public static function create($email = null)
    {
        $email = $email === null ? 'email@email.com' : $email;

        return new User($email, 'salt', 'password', 'fr');
    }
}
