<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class CreateRequest
{
    /** @var Event */
    public $event;

    /** @var Sheet */
    public $from;

    /** @var Sheet */
    public $to;

    /** @var Participant[] */
    public $participants = [];

    /** @var string */
    public $description;

    /** @var User */
    public $creator;

    /** @var bool */
    public $fromPriority;

    /**
     * @param Event $event
     * @param Sheet $from
     * @param Sheet $to
     * @param User  $creator
     */
    public function __construct(Event $event, Sheet $from, Sheet $to, User $creator)
    {
        $this->event        = $event;
        $this->from         = $from;
        $this->to           = $to;
        $this->creator      = $creator;
        $this->participants = [];
    }
}
