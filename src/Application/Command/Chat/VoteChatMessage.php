<?php

namespace Proximum\Vimeet\Application\Command\Chat;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\ChatMessageLinkableInterface;
use Proximum\Vimeet\Domain\Model\User;

class VoteChatMessage implements Command
{
    /** @var int */
    private $chatMessageId;

    /** @var ChatMessageLinkableInterface $chatMessageLinkableObject */
    private $chatMessageLinkableObject;

    /** @var User */
    private $user;

    /** @var string */
    private $type;

    public function __construct(int $chatMessageId, ChatMessageLinkableInterface $chatMessageLinkableObject, User $user, string $type)
    {
        $this->chatMessageId = $chatMessageId;
        $this->chatMessageLinkableObject = $chatMessageLinkableObject;
        $this->user = $user;
        $this->type = $type;
    }

    public function getChatMessageId(): int
    {
        return $this->chatMessageId;
    }

    public function getchatMessageLinkableObject(): ChatMessageLinkableInterface
    {
        return $this->chatMessageLinkableObject;
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
