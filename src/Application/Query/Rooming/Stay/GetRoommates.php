<?php

namespace Proximum\Vimeet\Application\Query\Rooming\Stay;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class GetRoommates implements Query
{
    /** @var User */
    public $user;

    /** @var Event */
    public $event;

    /** @var Sheet|null */
    public $sheet;

    public function __construct(User $user, Event $event, ?Sheet $sheet)
    {
        $this->user = $user;
        $this->event = $event;
        $this->sheet = $sheet;
    }
}
