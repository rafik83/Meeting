<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Register;

use Proximum\Vimeet\Domain\Model\User;

class RegisterNewUser
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
    public $locale;

    /**
     * @param string $email
     * @param string $locale
     */
    public function __construct($email, $locale)
    {
        $this->email  = $email;
        $this->locale = $locale;
    }
}
