<?php

namespace Proximum\Vimeet\Application\Query\User\Phone;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class UserEventPhoneQuery
{
    /** @var Event */
    public $event;

    /** @var User */
    public $user;

    /**
     * @param User  $user
     * @param Event $event
     */
    public function __construct(User $user, Event $event)
    {
        $this->user = $user;
        $this->event = $event;
    }
}
