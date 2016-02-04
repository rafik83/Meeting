<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use DateTime;

class ActivateAccountToken
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
     * @var Sheet
     */
    private $sheet;

    /**
     * @var DateTime
     */
    private $expireDate;

    /**
     * @param User   $user
     * @param string $token
     * @param Sheet  $sheet
     * @param string $expireDate
     */
    public function __construct(User $user, $token, Sheet $sheet, $expireDate)
    {
        $this->user       = $user;
        $this->token      = $token;
        $this->sheet      = $sheet;
        $this->expireDate = $expireDate;
    }
}
