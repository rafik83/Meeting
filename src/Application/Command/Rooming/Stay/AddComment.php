<?php

namespace Proximum\Vimeet\Application\Command\Rooming\Stay;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class AddComment implements Command
{
    /** @var Event */
    public $event;

    /** @var User */
    public $user;

    /** @var string */
    public $comment;

    public function __construct(Event $event, User $user, string $comment)
    {
        $this->event   = $event;
        $this->user    = $user;
        $this->comment = $comment;
    }
}
