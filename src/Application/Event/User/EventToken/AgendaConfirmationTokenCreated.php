<?php

namespace Proximum\Vimeet\Application\Event\User\EventToken;

use Proximum\Vimeet\Domain\Model\Event as DomainEvent;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\EventDispatcher\Event;

class AgendaConfirmationTokenCreated extends Event
{
    /** @var DomainEvent */
    private $event;

    /** @var User */
    private $user;

    /**
     * @param DomainEvent $event
     * @param User        $user
     */
    public function __construct(DomainEvent $event, User $user)
    {
        $this->event = $event;
        $this->user = $user;
    }

    /**
     * @return DomainEvent
     */
    public function getEvent(): DomainEvent
    {
        return $this->event;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }
}
