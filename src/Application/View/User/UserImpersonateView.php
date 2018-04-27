<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\User;

class UserImpersonateView
{
    /**
     * @var UserView
     */
    public $fromUser;

    /**
     * @var UserView
     */
    public $toUser;

    /**
     * @var string
     */
    public $exitLink;

    /**
     * UserImpersonateView constructor.
     *
     * @param UserView $fromUser
     * @param UserView $toUser
     * @param string   $exitLink
     */
    public function __construct(UserView $fromUser, UserView $toUser, $exitLink)
    {
        $this->exitLink = $exitLink;
        $this->fromUser = $fromUser;
        $this->toUser   = $toUser;
    }
}
