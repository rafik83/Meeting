<?php


namespace Proximum\Vimeet\Application\Query\Networking;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class GetSnippetQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var User */
    public $user;

    public function __construct(
        Event $event,
        User $user
    )
    {
        $this->event = $event;
        $this->user = $user;
    }
}
