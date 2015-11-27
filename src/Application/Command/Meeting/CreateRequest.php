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
use Proximum\Vimeet\Domain\Model\Sheet;

class CreateRequest
{
    public $from;

    public $to;

    public $fromParticipants;

    public $toParticipants;

    public $description;

    public $state;

    public $createdAt;

    public function __construct(Sheet $from, Sheet $to)
    {
        $this->from  = $from;
        $this->to    = $to;
        $this->state = Request::STATE_SENT;
    }
}
