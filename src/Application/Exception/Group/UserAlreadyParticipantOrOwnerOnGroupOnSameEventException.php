<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Exception\Group;

class UserAlreadyParticipantOrOwnerOnGroupOnSameEventException extends GroupException
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
        parent::__construct();

        $this->email = $email;
    }
}
