<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use Proximum\Vimeet\Domain\Model\Meeting;

class RequestSlotViewQuery
{
    /** @var Meeting\Request */
    public $meetingRequest;

    /**
     * @param Meeting\Request $meetingRequest
     */
    public function __construct(Meeting\Request $meetingRequest)
    {
        $this->meetingRequest = $meetingRequest;
    }
}
