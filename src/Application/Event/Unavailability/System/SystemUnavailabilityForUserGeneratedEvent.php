<?php

namespace Proximum\Vimeet\Application\Event\Unavailability\System;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\EventDispatcher\Event as EventDispatcherEvent;

class SystemUnavailabilityForUserGeneratedEvent extends EventDispatcherEvent
{
    /** @var User */
    public $user;

    /** @var Event */
    public $event;

    public function __construct(User $user, Event $event)
    {
        $this->user = $user;
        $this->event = $event;
    }
}
