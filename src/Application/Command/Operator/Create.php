<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Operator;

use Proximum\Vimeet\Domain\Model\Admin;

class Create
{
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
     * @var Admin
     */
    public $organizer;

    /**
     * @param Admin $organizer
     */
    public function __construct(Admin $organizer)
    {
        $this->organizer = $organizer;
        $this->password  = substr(md5(uniqid()), 0, 8);
    }
}
