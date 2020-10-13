<?php

namespace Proximum\Vimeet\Application\View\Networking;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Model\User;

class ChatSessionView
{
    /** @var User */
    public $otherUser;

    /** @var DateTimeInterface */
    public $latestMessageDate;

    /** @var int */
    public $messagesCount;

    /** @var int */
    public $newMessagesCount;

    public function __construct(
        User $otherUser,
        DateTimeInterface $latestMessageDate,
        int $messagesCount,
        int $newMessagesCount
    ) {
        $this->otherUser = $otherUser;
        $this->latestMessageDate = $latestMessageDate;
        $this->messagesCount = $messagesCount;
        $this->newMessagesCount = $newMessagesCount;
    }
}
