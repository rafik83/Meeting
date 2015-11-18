<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use DateInterval;
use DateTime;

class ForgottenPasswordToken
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var User
     */
    private $user;

    /**
     * @var string
     */
    private $token;

    /**
     * @var DateTime
     */
    private $expireDate;

    public function __construct(User $user)
    {
        $this->user       = $user;
        $this->token      = sha1(uniqid().$user->getId().uniqid());
        $expireDate       = new DateTime();
        $this->expireDate = $expireDate->add(new DateInterval('P1D'));
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return DateTime
     */
    public function getExpireDate()
    {
        return $this->expireDate;
    }
}
