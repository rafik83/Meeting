<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\User;

use Proximum\Vimeet\Domain\Model\AbstractUser;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class UserImpersonateViewQuery
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var AbstractUser
     */
    public $parentUser;

    /**
     * @var User
     */
    public $user;

    /**
     * @var string
     */
    public $exitRouteName;

    /**
     * @var array
     */
    public $routeParameters;

    /**
     * UserImpersonateViewQuery constructor.
     *
     * @param AbstractUser $parentUser
     * @param User         $user
     * @param Sheet        $sheet
     * @param string       $exitRouteName
     * @param array        $routeParameters
     */
    public function __construct(
        AbstractUser $parentUser,
        User $user,
        Sheet $sheet,
        $exitRouteName,
        array $routeParameters = []
    ) {
        $this->parentUser      = $parentUser;
        $this->user            = $user;
        $this->sheet           = $sheet;
        $this->exitRouteName   = $exitRouteName;
        $this->routeParameters = $routeParameters;
    }
}
