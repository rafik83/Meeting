<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;

class ApproveRequest
{
    /**
     * @var Request
     */
    public $request;

    /**
     * @var string
     */
    public $description;

    /**
     * @var Participant[]
     */
    public $participants;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request      = $request;
        $this->participants = [];
    }
}
