<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\ChatMessage;
use Proximum\Vimeet\Domain\Model\ChatMessageVote;
use Proximum\Vimeet\Domain\Model\User;

interface ChatMessageVoteRepositoryInterface
{
    public function add(ChatMessageVote $chatMessageVote): void;

    public function remove(ChatMessageVote $chatMessageVote): void;

    public function removeVotes(ChatMessage $chatMessage, User $user): void;

    public function getByChatMessageAndUser(ChatMessage $chatMessage, User $user, string $type): ?ChatMessageVote;

    public function getVotesCountByChatMessage(ChatMessage $chatMessage): array;

    public function getVotesByUser(string $chatLinkableObjectType, int $chatLinkableObjectId, User $user): array;
}
