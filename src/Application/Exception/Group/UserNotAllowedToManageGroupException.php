<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Exception\Group;

class UserNotAllowedToManageGroupException extends GroupException
{
    /** @var string */
    public $userName;

    /**
     * UserNotAllowedToManageGroupException constructor.
     *
     * @param string $userName
     */
    public function __construct($userName)
    {
        $this->userName = $userName;
    }
}
