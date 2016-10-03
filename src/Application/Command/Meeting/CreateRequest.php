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
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

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
    public $participants = [];

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $state;

    /**
     * @var User
     */
    public $creator;

    /**
     * @param Sheet $from
     * @param Sheet $to
     * @param User  $creator
     */
    public function __construct(Sheet $from, Sheet $to, User $creator)
    {
        $this->from         = $from;
        $this->to           = $to;
        $this->state        = Request::STATE_SENT;
        $this->creator      = $creator;
        $this->participants = [null];
    }
}
