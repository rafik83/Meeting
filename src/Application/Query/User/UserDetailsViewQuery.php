<?php

namespace Proximum\Vimeet\Application\Query\User;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class UserDetailsViewQuery implements Query
{
    /**
     * @var User
     */
    public $user;

    /**
     * @var Event
     */
    public $event;

    /**
     * UserDetailViewQuery constructor.
     *
     * @param User  $user
     * @param Event $event
     */
    public function __construct(User $user, Event $event)
    {
        $this->user = $user;
        $this->event = $event;
    }
}
