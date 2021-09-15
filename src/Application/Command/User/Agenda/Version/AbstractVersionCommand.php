<?php

namespace Proximum\Vimeet\Application\Command\User\Agenda\Version;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

abstract class AbstractVersionCommand implements Command
{
    /** @var Event */
    public $event;

    /** @var User */
    public $user;

    /**
     * @param Event $event
     * @param User  $user
     */
    public function __construct(Event $event, User $user)
    {
        $this->event = $event;
        $this->user = $user;
    }
}
