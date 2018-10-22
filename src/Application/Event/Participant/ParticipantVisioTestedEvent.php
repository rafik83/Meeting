<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event\Participant;

use Proximum\Vimeet\Domain\Model\Event as VimeetEvent;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\EventDispatcher\Event;

class ParticipantVisioTestedEvent extends Event
{
    /** @var User */
    public $user;

    /** @var Event */
    public $event;

    public function __construct(User $user, VimeetEvent $event)
    {
        $this->user = $user;
        $this->event = $event;
    }
}
