<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Admin;

use Proximum\Vimeet\Domain\Model\Admin;

class NewPassword
{
    /**
     * @var Admin
     */
    public $admin;

    /**
     * @var string
     */
    public $password;

    /**
     * @param Admin $admin
     */
    public function __construct(Admin $admin)
    {
        $this->admin = $admin;
    }
}
