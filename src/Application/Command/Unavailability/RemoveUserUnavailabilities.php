<?php

namespace Proximum\Vimeet\Application\Command\Unavailability;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class RemoveUserUnavailabilities implements Command
{
    /** @var User */
    public $user;

    /** @var Event */
    public $event;

    /** @var Sheet */
    public $sheet;

    public function __construct(User $user, Event $event, Sheet $sheet)
    {
        $this->user = $user;
        $this->event = $event;
        $this->sheet = $sheet;
    }
}
