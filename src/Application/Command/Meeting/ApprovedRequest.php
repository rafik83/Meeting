<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;

class ApprovedRequest
{
    /**
     * @var Request
     */
    public $request;

    /**
     * @var Participant[]
     */
    public $toParticipants;


    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->request->setState(Request::STATE_APPROVED);
    }
}
