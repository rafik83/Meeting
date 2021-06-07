<?php

namespace Proximum\Vimeet\Application\View\Networking;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Model\User;

class ChatSessionView
{
    /** @var User */
    public $otherUser;

    public string $avatarUrl;

    /** @var DateTimeInterface */
    public $latestMessageDate;

    /** @var int */
    public $messagesCount;

    /** @var int */
    public $newMessagesCount;

    public function __construct(
        User $otherUser,
        string $avatarUrl,
        DateTimeInterface $latestMessageDate,
        int $messagesCount,
        int $newMessagesCount
    ) {
        $this->otherUser = $otherUser;
        $this->avatarUrl = $avatarUrl;
        $this->latestMessageDate = $latestMessageDate;
        $this->messagesCount = $messagesCount;
        $this->newMessagesCount = $newMessagesCount;
    }
}
