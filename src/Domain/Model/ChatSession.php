<?php

namespace Proximum\Vimeet\Domain\Model;

class ChatSession implements ChatMessageLinkableInterface
{
    const OBJECT_TYPE = 'private_chat';

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
        return self::OBJECT_TYPE;
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
}
