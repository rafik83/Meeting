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

class ApproveRequest
{
    /**
     * @var Request
     */
    public $request;

    /**
     * @var Participant[]
     */
    public $toParticipants;

    /**
     * @var \DateTimeInterface
     */
    public $date;

    /**
     * @param Request            $request
     * @param \DateTimeInterface $date
     */
    public function __construct(Request $request, \DateTimeInterface $date)
    {
        $this->request = $request;
        $this->date    = $date;
    }
}
