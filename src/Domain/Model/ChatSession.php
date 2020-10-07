<?php

namespace Proximum\Vimeet\Domain\Model;

class ChatSession implements ChatMessageLinkableInterface
{
    /** @var int */
    private $id;

    /** @var Event */
    private $event;

    /** @var User */
    private $fromUser;

    /** @var User */
    private $toUser;

    public function __construct(Event $event, User $fromUser, User $toUser)
    {
        $this->event = $event;
        $this->fromUser = $fromUser;
        $this->toUser = $toUser;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getObjectType(): string
    {
        return 'private_chat';
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function isUserInChat(User $user): bool
    {
        $user->getId() === $this->toUser->getId() || $user->getId() === $this->fromUser->getId();
    }
}
