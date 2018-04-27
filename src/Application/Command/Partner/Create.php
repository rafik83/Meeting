<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Partner;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Type;

class Create
{
    /**
     * @var string
     */
    public $email;

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
    public $password;

    /**
     * @var Admin
     */
    public $organizer;

    /**
     * @var Type[]
     */
    public $types;

    /**
     * @param Admin $organizer
     */
    public function __construct(Admin $organizer)
    {
        $this->organizer = $organizer;
        $this->password  = substr(md5(uniqid()), 0, 8);
    }
}
