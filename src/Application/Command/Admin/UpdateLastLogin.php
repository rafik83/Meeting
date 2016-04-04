<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Admin;

use DateTimeInterface;

class UpdateLastLogin
{
    /**
     * @var string
     */
    public $email;

    /**
     * @var DateTimeInterface
     */
    public $date;

    /**
     * @param string            $email
     * @param DateTimeInterface $date
     */
    public function __construct($email, DateTimeInterface $date)
    {
        $this->email = $email;
        $this->date  = $date;
    }
}
