<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Agenda;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class UpdateAgendaConfirmedStatus
{
    /** @var User */
    public $user;

    /** @var Event */
    public $event;

    /**
     * @param Event $event
     * @param User  $user
     */
    public function __construct(Event $event, User $user)
    {
        $this->user = $user;
        $this->event = $event;
    }
}
