<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\ChatMessage;
use Proximum\Vimeet\Domain\Model\ChatMessageVote;
use Proximum\Vimeet\Domain\Model\User;

interface ChatMessageVoteRepositoryInterface
{
    public function add(ChatMessageVote $chatMessageVote);

    public function remove(ChatMessageVote $chatMessageVote);

    public function getByChatMessageAndUser(ChatMessage $chatMessage, User $user, string $type): ?ChatMessageVote;
}
