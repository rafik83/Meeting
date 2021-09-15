<?php

namespace Proximum\Vimeet\Application\Command\VideoConference;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class SetVisioTested implements Command
{
    /** @var Event */
    public $event;

    /** @var User */
    public $user;

    public function __construct(Event $event, User $user)
    {
        $this->event = $event;
        $this->user = $user;
    }
}
