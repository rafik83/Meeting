<?php

namespace Proximum\Vimeet\Application\Command\User;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class NewPassword implements Command
{
    /**
     * @var User
     */
    public $user;

    /**
     * @var string
     */
    public $password;

    /**
     * @var Event
     */
    public $event;

    /**
     * @param User  $user
     * @param Event $event
     */
    public function __construct(User $user, Event $event)
    {
        $this->user  = $user;
        $this->event = $event;
    }
}
