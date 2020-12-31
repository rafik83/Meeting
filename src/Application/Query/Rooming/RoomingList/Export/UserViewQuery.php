<?php

namespace Proximum\Vimeet\Application\Query\Rooming\RoomingList\Export;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class UserViewQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var User */
    public $user;

    /** @var string */
    public $locale;

    public function __construct(Event $event, User $user, string $locale)
    {
        $this->event = $event;
        $this->user = $user;
        $this->locale = $locale;
    }
}
