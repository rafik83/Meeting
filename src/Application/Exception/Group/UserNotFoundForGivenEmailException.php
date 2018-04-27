<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
