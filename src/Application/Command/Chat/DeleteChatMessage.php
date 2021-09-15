<?php

namespace Proximum\Vimeet\Application\Command\Chat;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class DeleteChatMessage implements Command
{
    /** @var int */
    public $messageId;

    /** @var User */
    public $user;

    /** @var User */
    public $event;

    public function __construct(
        int $messageId,
        User $user,
        Event $event
    ) {
        $this->messageId = $messageId;
        $this->user = $user;
        $this->event = $event;
    }
}
