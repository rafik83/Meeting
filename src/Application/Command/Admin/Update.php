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

class Update
{
    /**
     * @var Admin
     */
    public $admin;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $lastname;

    /**
     * @var string
     */
    public $firstname;

    /**
     * @var string
     */
    public $role;

    /**
     * @var array
     */
    public $events;

    /**
     * @param Admin $admin
     */
    public function __construct(Admin $admin)
    {
        $this->admin     = $admin;
        $this->email     = $admin->getEmail();
        $this->lastname  = $admin->getLastname();
        $this->firstname = $admin->getFirstname();
        $this->role      = $admin->getRole();
        $this->events    = $admin->getEvents()->toArray();
    }
}
