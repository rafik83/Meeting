<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\ChatSession;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

interface ChatSessionRepositoryInterface
{
    public function add(ChatSession $chatSession);

    public function findOneByEventAndUsers(Event $event, User $aUser, User $anotherUser): ?ChatSession;

    public function findOneById(int $id): ?ChatSession;
}
