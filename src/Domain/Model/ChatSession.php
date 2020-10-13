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

    /** @var array */
    private $unreadMessages;

    public function __construct(Event $event, User $fromUser, User $toUser)
    {
        $this->event = $event;
        $this->fromUser = $fromUser;
        $this->toUser = $toUser;
        $this->unreadMessages = [];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getObjectType(): string
    {
        return ChatMessage::TYPE_PRIVATE_CHAT;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getOtherUser(User $currentUser): User
    {
        return $currentUser->getId() === $this->fromUser->getId() ? $this->toUser : $this->fromUser;
    }

    public function isUserInChat(User $user): bool
    {
        return $user->getId() === $this->toUser->getId() || $user->getId() === $this->fromUser->getId();
    }

    public function incrementUnreadMessages(User $user): void
    {
        $this->unreadMessages[$user->getId()] = ($this->unreadMessages[$user->getId()] ?? 0) + 1;
    }

    public function resetUnreadMessages(User $user): void
    {
        $this->unreadMessages[$user->getId()] = 0;
    }

    public function getUnreadMessages(User $user): int
    {
        return $this->unreadMessages[$user->getId()] ?? 0;
    }
}
