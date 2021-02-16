<?php

namespace Proximum\Vimeet\Application\Event\User\Phone;

use Proximum\Vimeet\Domain\Model\Event as EventModel;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\EventDispatcher\Event;

class PhoneValidatedEvent extends Event
{
    /** @var User */
    private $user;

    /** @var EventModel */
    private $event;

    /**
     * @param User       $user
     * @param EventModel $event
     */
    public function __construct(User $user, EventModel $event)
    {
        $this->user = $user;
        $this->event = $event;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return EventModel
     */
    public function getEvent(): EventModel
    {
        return $this->event;
    }
}
