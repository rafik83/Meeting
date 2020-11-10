<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\ChatSession;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

interface ChatSessionRepositoryInterface
{
    public function add(ChatSession $chatSession): ChatSession;

    public function update():void;

    public function findOneByEventAndUsers(Event $event, User $aUser, User $anotherUser): ?ChatSession;

    /**
     *   @return array{otherUser: User, latestMessageDate: DateTimeInterface, messagesCount: int, unreadMessages: array}[]
     */
    public function findSessionsByEventAndUser(Event $event, User $user): array;

    public function findIdsByEventAndUser(Event $event, User $user): array;

    public function findOneById(int $id): ?ChatSession;

    public function hasMessageFromUser (ChatSession $chatSession, User $user): bool;

    public function countCallVisioByEvent(Event $event):int;
}
