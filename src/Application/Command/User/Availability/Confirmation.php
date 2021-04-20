<?php

namespace Proximum\Vimeet\Application\Command\User\Availability;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class Confirmation implements Command
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
