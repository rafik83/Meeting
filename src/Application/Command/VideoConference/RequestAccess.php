<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\VideoConference;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\User;

class RequestAccess
{
    /**
     * @var Meeting
     */
    public $meeting;

    /**
     * @var User
     */
    public $user;

    /**
     * RequestAccess constructor.
     *
     * @param Meeting $meeting
     * @param User    $user
     */
    public function __construct(Meeting $meeting, User $user)
    {
        $this->meeting = $meeting;
        $this->user    = $user;
    }
}
