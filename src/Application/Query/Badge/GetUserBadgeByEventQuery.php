<?php

namespace Proximum\Vimeet\Application\Query\Badge;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class GetUserBadgeByEventQuery extends AbstractGetBadgeByEventQuery
{
    /** @var User */
    public $user;

    public function __construct(Event $event, User $user)
    {
        $this->user = $user;

        parent::__construct($event);
    }
}
