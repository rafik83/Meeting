<?php

namespace Proximum\Vimeet\Application\Event\User\Event;

use Proximum\Vimeet\Domain\Model\Event as DomainEvent;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\EventDispatcher\Event;

class UpdatedEvent extends Event
{
    /** @var User */
    public $user;

    /** @var DomainEvent */
    public $event;

    public function __construct(User $user, DomainEvent $event)
    {
        $this->user = $user;
        $this->event = $event;
    }
}
