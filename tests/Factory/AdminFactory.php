<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Factory;

use Proximum\Vimeet\Domain\Model\Admin;

class AdminFactory
{
    /**
     * @param string $email
     *
     * @return Admin
     */
    public static function create($email = 'admin@vimeet.events')
    {
        $now = new \DateTime();

        return new Admin($email, 'salt', 'password', 'fr', 'vincent', 'larose', 'ROLE_ADMIN', $now);
    }
}
