<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting;

use DateTime;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;

class CreateRequest
{
    /**
     * @var Sheet
     */
    public $from;

    /**
     * @var Sheet
     */
    public $to;

    /**
     * @var Participant[]
     */
    public $fromParticipants = [];

    /**
     * @var Participant[]
     */
    public $toParticipants = [];

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $state;

    /**
     * @var DateTime
     */
    public $createdAt;

    /**
     * @param Sheet    $from
     * @param Sheet    $to
     * @param DateTime $createdAt
     */
    public function __construct(Sheet $from, Sheet $to, DateTime $createdAt)
    {
        $this->from = $from;
        $this->to = $to;
        $this->state = Request::STATE_SENT;
        $this->createdAt = $createdAt;
    }
}
