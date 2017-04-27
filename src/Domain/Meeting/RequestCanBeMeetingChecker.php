<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting;

class RequestCanBeMeetingChecker
{
    /**
     * @param Meeting\Request $meetingRequest
     *
     * @return bool
     */
    public function handle(Meeting\Request $meetingRequest)
    {
        return true === $meetingRequest->getFromSheet()->attend() && true === $meetingRequest->getToSheet()->attend();
    }
}
