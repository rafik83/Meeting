<?php

namespace Proximum\Vimeet\Application\Event\User;

use Proximum\Vimeet\Domain\Model\Admin;
use Symfony\Component\EventDispatcher\Event as EventDispatcherEvent;

class AdminTemporarilyDisabledEvent extends EventDispatcherEvent
{
    /** @var Admin */
    private $admin;

    /** @var string */
    private $locale;

    public function __construct(Admin $admin, string $locale)
    {
        $this->admin = $admin;
        $this->locale = $locale;
    }

    public function getAdmin(): Admin
    {
        return $this->admin;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }
}
