<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Planning\Day;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\User;

class MeetingViewQuery
{
    /** @var Meeting */
    public $meeting;

    /**
     * @var User */
    public $user;

    /**
     * @param Meeting $meeting
     * @param User    $user
     */
    public function __construct(Meeting $meeting, User $user)
    {
        $this->meeting = $meeting;
        $this->user = $user;
    }
}
