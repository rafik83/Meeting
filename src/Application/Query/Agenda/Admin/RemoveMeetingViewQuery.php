<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Sheet;

class RemoveMeetingViewQuery
{
    /**
     * @var Meeting
     */
    public $meeting;

    /**
     * @var Admin
     */
    public $user;

    /**
     * RemoveMeetingViewQuery constructor.
     *
     * @param Meeting $meeting
     * @param Admin   $user
     */
    public function __construct(Meeting $meeting, Admin $user)
    {
        $this->meeting = $meeting;
        $this->user    = $user;
    }
}
