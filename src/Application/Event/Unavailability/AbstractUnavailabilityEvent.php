<?php

namespace Proximum\Vimeet\Application\Event\Unavailability;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\EventDispatcher\Event as EventDispatcherEvent;

abstract class AbstractUnavailabilityEvent extends EventDispatcherEvent
{
    /** @var User */
    public $user;

    /** @var Event */
    public $event;

    /**
     * @param User  $user
     * @param Event $event
     */
    public function __construct(User $user, Event $event)
    {
        $this->user = $user;
        $this->event = $event;
    }
}
