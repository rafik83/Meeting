<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Admin\Version;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class RetrieveUserAgendaVersion implements Query
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
