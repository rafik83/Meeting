<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\User;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class ActivateAccountPassword
{
    /**
     * @var User
     */
    public $user;

    /**
     * @var string
     */
    public $password;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @param User  $user
     * @param Sheet $sheet
     */
    public function __construct(User $user, Sheet $sheet)
    {
        $this->user  = $user;
        $this->sheet = $sheet;
    }
}
