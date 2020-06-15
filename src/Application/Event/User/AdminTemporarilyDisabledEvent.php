<?php

namespace Proximum\Vimeet\Application\Event\User;

use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\EventDispatcher\Event as EventDispatcherEvent;

class AdminTemporarilyDisabledEvent extends EventDispatcherEvent
{
    /** @var User */
    private $admin;

    /** @var string */
    private $locale;

    public function __construct(User $admin, string $locale)
    {
        $this->admin = $admin;
        $this->locale = $locale;
    }

    public function getAdmin(): User
    {
        return $this->admin;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }
}
