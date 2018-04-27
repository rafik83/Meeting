<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting;

class CreateRequestResult
{
    /**
     * @var Meeting\Request
     */
    public $meetingRequest;

    /**
     * @param Meeting\Request $meetingRequest
     */
    public function __construct(Meeting\Request $meetingRequest)
    {
        $this->meetingRequest = $meetingRequest;
    }
}
