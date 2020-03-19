<?php

namespace Proximum\Vimeet\Application\Event\User;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\EventDispatcher\Event as EventDispatcherEvent;

class UserTemporarilyDisabledEvent extends EventDispatcherEvent
{
    /** @var User */
    private $user;

    /** @var Event */
    private $event;

    public function __construct(Event $event, User $user)
    {
        $this->event = $event;
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }
}
