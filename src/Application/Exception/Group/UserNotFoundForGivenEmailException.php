<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Exception\Group;

class UserNotFoundForGivenEmailException extends GroupException
{
    /** @var string */
    public $email;

    /**
     * UserNotFoundForGivenEmailException constructor.
     *
     * @param string $email
     */
    public function __construct($email)
    {
        $this->email = $email;
    }
}
