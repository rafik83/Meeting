<?php

namespace Proximum\Vimeet\Application\Event\User;

use Proximum\Vimeet\Domain\Model\Event as DomainEvent;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\EventDispatcher\Event;

class RegistrationEvent extends Event
{
    /** @var User */
    public $user;

    /** @var DomainEvent */
    public $event;

    /**
     * @param DomainEvent $event
     * @param User        $user
     */
    public function __construct(DomainEvent $event, User $user)
    {
        $this->event = $event;
        $this->user = $user;
    }
}
