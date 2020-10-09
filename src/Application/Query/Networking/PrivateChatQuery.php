<?php


namespace Proximum\Vimeet\Application\Query\Networking;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class PrivateChatQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var User */
    public $toUser;

    /** @var User */
    public $fromUser;

    public function __construct(
        Event $event,
        User $fromUser,
        User $toUser
    )
    {
        $this->event = $event;
        $this->toUser = $toUser;
        $this->fromUser = $fromUser;
    }
}
