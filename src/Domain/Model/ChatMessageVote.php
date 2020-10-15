<?php

namespace Proximum\Vimeet\Domain\Model;

use Proximum\Vimeet\Domain\Model\ChatMessage;
use Proximum\Vimeet\Domain\Model\User;

class ChatMessageVote
{
    /** @var int */
    private $id;

    /** @var ChatMessage */
    private $chatMessage;

    /** @var User */
    private $user;

    /** @var string */
    private $type;

    public function __construct(ChatMessage $chatMessage, User $user, string $type)
    {
        $this->chatMessage = $chatMessage;
        $this->user = $user;
        $this->type = $type;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getChatMessage(): ChatMessage
    {
        return $this->chatMessage;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
