<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use Proximum\Vimeet\Domain\Model\User\Account;

/**
 * "Compte utilisateur".
 */
class User extends AbstractUser
{
    /**
     * @var Account
     */
    private $account;

    /**
     * @var bool
     */
    private $welcomed = false;

    /**
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param Account $account
     *
     * @return User
     */
    public function setAccount(Account $account)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * @return bool
     */
    public function isWelcomed()
    {
        return true === $this->welcomed;
    }

    /**
     * @return User
     */
    public function welcome()
    {
        $this->welcomed = true;

        return $this;
    }
}
