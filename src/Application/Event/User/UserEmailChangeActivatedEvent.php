<?php

namespace Proximum\Vimeet\Application\Event\User;

use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\EventDispatcher\Event;

class UserEmailChangeActivatedEvent extends Event
{
    /** @var User */
    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
