<?php

namespace Proximum\Vimeet\Application\Command\User\Event\Token;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class UpdateAgendaConfirmation
{
    /** @var Event */
    public $event;

    /** @var User */
    public $user;

    /** @var string|null */
    public $status;

    /**
     * @param Event       $event
     * @param User        $user
     * @param string|null $status
     */
    public function __construct(Event $event, User $user, string $status = null)
    {
        $this->event = $event;
        $this->user = $user;
        $this->status = $status;
    }
}
