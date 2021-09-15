<?php

namespace Proximum\Vimeet\Application\Event\User;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class ResetPasswordConfirmEvent extends \Symfony\Component\EventDispatcher\Event
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var string
     */
    private $locale;

    /**
     * @param User   $user
     * @param Event  $event
     * @param string $locale
     */
    public function __construct(User $user, Event $event, $locale)
    {
        $this->user   = $user;
        $this->event  = $event;
        $this->locale = $locale;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }
}
