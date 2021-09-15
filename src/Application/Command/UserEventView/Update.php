<?php

namespace Proximum\Vimeet\Application\Command\UserEventView;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class Update implements Command
{
    /** @var User */
    public $user;

    /** @var Event */
    public $event;

    public function __construct(User $user, Event $event)
    {
        $this->user = $user;
        $this->event = $event;
    }
}
