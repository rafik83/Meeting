<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\User;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;

class Participate
{
    /**
     * @var User
     */
    public $user;

    /**
     * @var Event
     */
    public $event;

    /**
     * @var Type
     */
    public $type;

    /**
     * @var array
     */
    public $data;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var bool
     */
    public $owner;

    /**
     * @param User  $user
     * @param Event $event
     * @param Type  $type
     * @param array $data
     */
    public function __construct(User $user, Event $event, Type $type, array $data)
    {
        $this->user   = $user;
        $this->event  = $event;
        $this->type   = $type;
        $this->data   = $data;
        $this->owner  = true;
    }
}
