<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\User;

use Proximum\Vimeet\Domain\Model\User;

class ChangePassword
{
    /**
     * @var User
     */
    public $user;

    /**
     * @var string
     */
    public $currentPassword;

    /**
     * @var string
     */
    public $plainPassword;

    /**
     * @param User   $user
     * @param string $currentPassword
     * @param string $plainPassword
     */
    public function __construct(User $user, $currentPassword, $plainPassword)
    {
        $this->user            = $user;
        $this->currentPassword = $currentPassword;
        $this->plainPassword   = $plainPassword;
    }
}
