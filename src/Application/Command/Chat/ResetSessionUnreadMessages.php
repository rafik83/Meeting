<?php

namespace Proximum\Vimeet\Application\Command\Chat;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\ChatSession;
use Proximum\Vimeet\Domain\Model\User;

class ResetSessionUnreadMessages implements Command
{
    /** @var ChatSession */
    public $chatSession;

    /** @var User */
    public $user;

    public function __construct(ChatSession $chatSession, User $user)
    {
        $this->chatSession = $chatSession;
        $this->user = $user;
    }
}
