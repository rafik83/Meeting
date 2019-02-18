<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\User\ActivateAccount;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class SendActivateAccountFromLoginToken
{
    /** @var Sheet */
    public $sheet;

    /** @var User */
    public $user;

    public function __construct(Sheet $sheet, User $user)
    {
        $this->sheet = $sheet;
        $this->user = $user;
    }
}
