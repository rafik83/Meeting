<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Exception\Group;

class UserAlreadyGroupManagerOnSameEventException extends GroupException
{
    /** @var string */
    public $email;

    /**
     * UserNotAllowedToManageGroupException constructor.
     *
     * @param string|null $email
     */
    public function __construct($email = null)
    {
        $this->email = $email;
    }
}
