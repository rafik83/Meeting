<?php

namespace Proximum\Vimeet\Application\Event\User;

use Proximum\Vimeet\Domain\Model\Event as ProximumEvent;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\EventDispatcher\Event;

class RegisteredEvent extends Event
{
    /**
     * @var ProximumEvent
     */
    private $event;

    /**
     * @var User
     */
    private $user;

    /**
     * @var string
     */
    private $locale;

    /**
     * RegisteredEvent constructor.
     *
     * @param ProximumEvent $event
     * @param User          $user
     * @param string        $locale
     */
    public function __construct(ProximumEvent $event, User $user, $locale)
    {
        $this->event       = $event;
        $this->user        = $user;
        $this->locale      = $locale;
    }

    /**
     * @return ProximumEvent
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }
}
